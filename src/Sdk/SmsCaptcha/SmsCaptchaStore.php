<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/28
 * Time: 下午8:33
 */

namespace Zan\Framework\Sdk\SmsCaptcha;


use Zan\Framework\Store\Facade\Cache;
use Zan\Framework\Utilities\Types\Time;

class SmsCaptchaStore
{
    const SALT                              = 'kdt_hello_world';
    const SMS_CAPTCHA_CACHE_KEY             = 'common.smscaptcha.response';
    const SMS_CAPTCHA_COUNT_CACHE_KEY       = 'common.smscaptcha.count';
    const SMS_CAPTCHA_TIME_CACHE_KEY        = 'common.smscaptcha.time';
    const SMS_CAPTCHA_TOTAL_CACHE_KEY       = 'common.smscaptcha.total';
    const SMS_CAPTCHA_BIZ_COUNT_CACHE_KEY   = 'common.smscaptcha.bizcount';

    public static function getCode($mobile, $biz)
    {
        yield Cache::get(self::SMS_CAPTCHA_CACHE_KEY, [$mobile, $biz]);
    }

    public static function setCode($mobile, $biz, $code)
    {
        yield Cache::set(self::SMS_CAPTCHA_CACHE_KEY, [$mobile, $biz], $code);
    }

    public static function delCode($mobile, $biz)
    {
        yield Cache::del(self::SMS_CAPTCHA_CACHE_KEY, [$mobile, $biz]);
    }

    public static function getCount($mobile, $biz)
    {
        $count = (yield Cache::get(self::SMS_CAPTCHA_COUNT_CACHE_KEY, [$mobile, $biz]));
        yield intval($count);
    }

    public static function setCount($mobile, $biz, $count)
    {
        yield Cache::set(self::SMS_CAPTCHA_COUNT_CACHE_KEY, [$mobile, $biz], $count);
    }

    public static function delCount($mobile, $biz)
    {
        yield Cache::del(self::SMS_CAPTCHA_COUNT_CACHE_KEY, [$mobile, $biz]);
    }

    public static function getBizCount($mobile, $biz)
    {
        $count = (yield Cache::get(self::SMS_CAPTCHA_BIZ_COUNT_CACHE_KEY, [$mobile, $biz]));
        yield intval($count);
    }

    public static function setBizCount($mobile, $biz, $count)
    {
        yield Cache::set(self::SMS_CAPTCHA_BIZ_COUNT_CACHE_KEY, [$mobile, $biz], $count);
    }

    public static function delBizCount($mobile, $biz)
    {
        yield Cache::del(self::SMS_CAPTCHA_BIZ_COUNT_CACHE_KEY, [$mobile, $biz]);
    }

    public static function getTotal($mobile, $biz)
    {
        $total = (yield Cache::get(self::SMS_CAPTCHA_TOTAL_CACHE_KEY, $mobile));
        yield intval($total);
    }

    public static function setTotal($mobile, $biz, $total)
    {
        yield Cache::set(self::SMS_CAPTCHA_TOTAL_CACHE_KEY, $mobile, $total);
    }

    public static function delTotal($mobile, $biz)
    {
        yield Cache::del(self::SMS_CAPTCHA_TOTAL_CACHE_KEY, $mobile);
    }

    public static function getTime($mobile, $biz)
    {
        $time = (yield Cache::get(self::SMS_CAPTCHA_TIME_CACHE_KEY, $mobile));
        yield intval($time);
    }

    public static function setTime($mobile, $biz)
    {
        yield Cache::set(self::SMS_CAPTCHA_TIME_CACHE_KEY, $mobile, Time::current(true));
    }

    public static function delTime($mobile, $biz)
    {
        yield Cache::del(self::SMS_CAPTCHA_TIME_CACHE_KEY, $mobile);
    }

    public static function initCaptcha($response, $mobile, $biz)
    {
        $code = self::generateCacheValue($response, $mobile, $biz);
        yield self::setCode($mobile, $biz, $code);
        yield self::setTime($mobile, $biz);
        yield self::setCount($mobile, $biz, 1);
    }

    public static function clear($bizMap, $mobile, $biz)
    {
        foreach($bizMap as $biz) {
            yield self::delBizCount($mobile, $biz);
            yield self::delCount($mobile, $biz);
        }

        yield self::delTotal($mobile, $biz);
        yield self::delTime($mobile, $biz);
    }

    public static function generateCacheValue($response, $mobile, $biz)
    {
        return md5($response . $mobile . $biz . self::SALT);
    }
}