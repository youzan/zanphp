<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/28
 * Time: 下午8:34
 */

namespace Zan\Framework\Sdk\SmsCaptcha;


use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Utilities\Types\Time;

class SmsCaptchaValidation
{
    const MAX_INVALID_COUNT  = 10;
    const MAX_BEHIND_SECONDS = 60;
    const MAX_TOTAL          = 9;
    const MAX_BIZ_COUNT      = 2;

    public static function validCount($mobile, $biz)
    {
        $usedCount = (yield SmsCaptchaStore::getCount($mobile, $biz));

        if ($usedCount >= self::MAX_INVALID_COUNT) {
            throw new SmsCaptchaException('验证失败次数超过限制，请24小时之后重试！');
        }
    }

    public static function checkTime($mobile, $biz)
    {
        $time = (yield SmsCaptchaStore::getTime($mobile, $biz));

        if((Time::current(true) - $time) <= self::MAX_BEHIND_SECONDS){
            throw new SmsCaptchaException('短信发送太频繁，请稍后再试！');
        }
    }

    public static function checkBizSend($mobile, $biz)
    {
        $count = (yield SmsCaptchaStore::getBizCount($mobile, $biz));

        if($count > self::MAX_BIZ_COUNT){
            throw new SmsCaptchaException('您的短信发送过于频繁，请稍后再试');
        }

        yield SmsCaptchaStore::setBizCount($mobile, $biz, $count + 1);
    }

    public static function checkTotal($mobile, $biz)
    {
        $total = (yield SmsCaptchaStore::getTotal($mobile, $biz));

        if($total > self::MAX_TOTAL){
            throw new SmsCaptchaException('短信验证码请求次数超限，请24小时之后重试！');
        }

        yield SmsCaptchaStore::setTotal($mobile, $biz, $total + 1);
    }

    public static function checkCode($response, $mobile, $biz)
    {
        if( !in_array(Config::get('run_mode'), ['online', 'pre'], true)) {
            return true;
        }

        $userCode = SmsCaptchaStore::generateCacheValue($response, $mobile, $biz);
        $cacheCode = (yield SmsCaptchaStore::getCount($mobile, $biz));

        if(!$cacheCode && $biz == 9) {
            $userCode = SmsCaptchaStore::generateCacheValue($response, $mobile, 15);
            $cacheCode = (yield SmsCaptchaStore::getCount($mobile, $biz));
        }

        return $userCode === $cacheCode;
    }
}