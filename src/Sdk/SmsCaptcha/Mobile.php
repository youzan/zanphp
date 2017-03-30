<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/29
 * Time: 上午11:27
 */

namespace Zan\Framework\Sdk\SmsCaptcha;


class Mobile
{
    public static function sanitize($mobile)
    {
        //国内手机号 不能传＋86/86 给java服务
        if(Mobile::isDomesticMobile($mobile)) {
            $mobile = Mobile::removeCountyCode($mobile);
            if (!Mobile::isMobile($mobile)) {
                throw new SmsCaptchaException('手机号码，咋就不对呢');
            }
        }

        return $mobile;
    }

    private static function isMobile($mobile)
    {
        return strlen($mobile) === 11 && is_numeric($mobile) && preg_match('/^1\d{10}$/', $mobile);
    }

    // 国际化改造，手机号默认是国内的
    private static function isDomesticMobile($mobile)
    {
        $ret = true;
        //国际手机业务 例如  83-0760714  86-13810015678
        $pos = strpos($mobile, '-');
        $countryCode = $pos === false ? 86 : substr($mobile, 0, $pos);
        if (!in_array($countryCode, ['86', '+86'])) {
            $ret = false;
        }

        return $ret;
    }

    private static function removeCountyCode($mobile)
    {
        $pos = strpos($mobile, '-');
        if ($pos !== false) {
            $mobile = substr($mobile, $pos + 1);;
        }
        return $mobile;
    }
}