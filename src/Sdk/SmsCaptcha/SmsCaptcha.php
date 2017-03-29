<?php

namespace Zan\Framework\Sdk\SmsCaptcha;


use Zan\Framework\Network\Http\Middleware\Session;
use Zan\Framework\Sdk\Sms\Channel;
use Zan\Framework\Sdk\Sms\MessageContext;
use Zan\Framework\Sdk\Sms\Recipient;
use Zan\Framework\Sdk\Sms\SmsService;
use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Time;


/**
 * Class SmsCaptcha
 * @package Zan\Framework\Sdk\SmsCaptcha
 *
 * 从IRON移植过来的短信验证码SDK, 接口不变, 方便迁移代码
 */
class SmsCaptcha
{
    const SESSION_SALT = 'smscaptcha_salt';
    const SESSION_KEY  = 'smscaptcha_session_key_for_pc';

    const BIZ_ID_UPDATE_CASH_ACCOUNT   = 9; //修改提现到 银行卡账号
    const BIZ_ID_ADD_CASH_ACCOUNT      = 15; //增加提现到 银行卡账号

    public $bizMap = [
        'kdt_account_captcha' => 3,//注册时
        'reset_team_mobile_captcha' => 4,//修改店铺联系方式时
        'set_trade_captcha' => 5,//设置提现账户时
        'create_team_captcha' => 6,//创建店铺时
        'create_company_captcha' => 7,//创建公司时
        'reset_team_certification_captcha' => 8,//修改认证联系方式时
        'update_cash_account' => 9, //修改提现到 银行卡账号
        'certificate_team_captcha' => 10, //个人快速认证
        'reset_account_passwd' => 11, //重置密码
        'kdt_account_change_mobile' => 12, //修改手机号
        'kdt_shop_delete' => 13, //删除店铺
        'kdt_weixin_unbund' =>14, //解绑微信
        'add_cash_account' =>15, //增加提现到 银行卡账号
        'team_close' =>16, //店铺打烊
        'kdt_add_admin' =>17, //店铺添加高级管理员
        'scrm_member_verification' =>18, //scrm 会员卡
        'activation_code_order' =>19, //激活码批量采购
    ];

    public $mobile;
    public $biz;
    public $ip;
    public $sendTimes;
    public $platform;
    public $subFrom;

    public function __construct(array $config)
    {
        if(!in_array($config['biz'], array_keys($this->bizMap))) {
            throw new SmsCaptchaException('亲，业务类型错啦！');
        }

        $config['mobile'] = Mobile::sanitize($config['mobile']);

        $sendTimes = isset($config['send_times']) ? intval($config['send_times']) : 0;

        $this->mobile    = $config['mobile'];
        $this->biz       = $this->bizMap[$config['biz']];
        $this->ip        = isset($config['ip']) ? $config['ip'] : 'unknown';
        $this->sendTimes = ($sendTimes - 1) < 0 ? 0 : $sendTimes - 1;
        $this->platform  = isset($config['platform']) ? $config['platform'] : 'unset'; //pc, app 平台
        $this->subFrom   = isset($config['sub_from']) ? $config['sub_from'] : 'unset'; //wsc, wxd, pf,  业务线
    }

    public function send()
    {
        yield $this->sendCheck();

        $smsCaptcha = $this->generateCaptcha();

        try {
            $messageParams = [
                'code' => $smsCaptcha,
                'biz' => $this->biz,
                'platform' => $this->platform,
                'sub_from' => $this->subFrom,
            ];

            SmsService::getInstance()->send(
                self::getMessageContextForSmsCaptcha($messageParams),
                [new Recipient(Channel::SMS, $this->mobile)]
            );

        } catch (\Exception $e) {
            sys_error("send sms fail:" . json_encode([
                    'ip'     => $this->ip,
                    'mobile' => $this->mobile,
                    'biz'    => $this->biz,
                    'send_times' => $this->sendTimes,
                    'captcha_code' => $smsCaptcha,
                    'platform' => $this->platform,
                    'sub_from' => $this->subFrom,
                    'result' => false,
                    'errMsg' => $e->getMessage(),
                ]));

            throw new SmsCaptchaException('短信发送失败，请稍后重试！');
        }

        yield SmsCaptchaStore::initCaptcha($smsCaptcha, $this->mobile, $this->biz);
    }

    public static function setSmsSession()
    {
        yield Session::set(self::SESSION_KEY, self::SESSION_SALT);
    }

    public function valid($response, $invalid = true)
    {
        yield SmsCaptchaValidation::validCount($this->mobile, $this->biz);

        $eq = (yield SmsCaptchaValidation::checkCode($response, $this->mobile, $this->biz));
        if ($eq) {
            yield $this->validSuccess($invalid);
            yield true;
        } else {
            yield $this->validFailed();
            yield false;
        }
    }

    public function smsCaptchaInvalid()
    {
        yield $this->validSuccess(true);
    }

    public function validFailed()
    {
        $usedCount = 0;
        $count = (yield SmsCaptchaStore::getCount($this->mobile, $this->biz));

        if ($count) {
            $usedCount = $count + 1;
        }

        yield SmsCaptchaStore::setCount($this->mobile, $this->biz, $usedCount);
    }

    public function validSuccess($invalid)
    {
        if($invalid) {
            //给app预留一个开关，控制验证码是否失效
            yield SmsCaptchaStore::delCode($this->mobile, $this->biz);
            yield SmsCaptchaStore::delCount($this->mobile, $this->biz);
        }
    }

    /**
     * 需要根据不通的biz id ,确定短信模版的类型；
     * @param $messageParams
     * @return MessageContext
     */
    public function getMessageContextForSmsCaptcha($messageParams)
    {
        $biz = Arr::get($messageParams, 'biz', 0);

        //Smscaptcha biz_id类型
        switch($biz){
            case self::BIZ_ID_UPDATE_CASH_ACCOUNT: //修改银行卡
                $templateName = 'safeCode4ModCard';
                break;
            case self::BIZ_ID_ADD_CASH_ACCOUNT: //添加银行卡
                $templateName = 'safeCode4AddCard';
                break;
            default:
                $templateName = 'verificationCode';
                break;
        }

        return new MessageContext($templateName, $messageParams);
    }

    private function sendCheck()
    {
        $mobile = $this->mobile;
        $biz = $this->biz;

        yield SmsCaptchaValidation::checkTime($mobile, $biz);

        //同一号码同一个业务一小时只能发送不超过3次
        yield SmsCaptchaValidation::checkBizSend($mobile, $biz);

        //同一号码一天只能发送不超过350次
        yield SmsCaptchaValidation::checkTotal($mobile, $biz);
    }

    private function generateCaptcha()
    {
        return substr(crc32(ceil(Time::current(true) / 60) . $this->mobile), -6, 6);
    }
}