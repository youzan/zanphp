<?php

namespace Zan\Framework\Sdk\Cdn;

use Qiniu\Auth;

class Qiniu
{

    private static $config;



    public static function setConfig(array &$config){
        self::$config = &$config;
    }

    /**
     * get the qiniu url
     * @param  [string] $url [qiniu key]
     * @param  [string] $fop [process imGage]
     * @return [string]
     */
    public static function site($url_string = '', $fop = '')
    {
        if (strpos($url_string, 'koudaitong') !== FALSE
            || strpos($url_string, 'kdt') !== FALSE
            || strpos($url_string, 'yzcdn') !== FALSE
            || strpos($url_string, 'upload_files') !== FALSE
            || strpos($url_string, 'public_files') !== FALSE
        ) {
            $bucket = self::getBucketByUrl($url_string);
            // process fop
            foreach (self::$config['bucket'][$bucket]['fop'] as $key => $value) {
                if (strpos($url_string, $value) !== FALSE) {
                    $fop = $key;
                    $url_string = str_replace($value, '', $url_string);
                    break;
                }
            }
            $url_array = parse_url($url_string);
            $fop = self::getFop($bucket, $fop);
            if (self::isPublicBucket($bucket)) {
                return self::getPublicUrl($bucket, trim($url_array['path'], '/'), $fop);
            }

            return self::getPrivateUrl($bucket, trim($url_array['path'], '/'), $fop);
        }

        return self::$config['no_pic_url'];
    }

    /**
     * get qiniu public url
     * @param  [string] $bucket [bucket name]
     * @param  [type] $key    	[key name]
     * @param  string $fop    	[fop name such as thumbnails]
     * @return [string]         [public url]
     */
    public static function getPublicUrl($bucket, $key, $fop = '')
    {
        $publicUrl = self::makeBaseUrl(self::getDomain($bucket), $key) ;

        return urldecode($publicUrl) . "{$fop}";
    }

    /**
     * get qiniu private url
     * @param  [string] $bucket [bucket name]
     * @param  [type] $key    	[key name]
     * @param  string $fop    	[fop name such as thumbnails]
     * @return [string]         [private url]
     */
    public static function getPrivateUrl($bucket, $key, $fop ='')
    {
        $baseUrl = self::makeBaseUrl(self::getDomain($bucket), $key) . $fop;
        $baseUrl = str_replace("http://", "https://", $baseUrl);
        $expires = self::$config['bucket'][$bucket]['expires'];

        $accessKey = self::$config['access_key'];
        $secretKey = self::$config['secret_key'];
        $auth = new Auth($accessKey,$secretKey);
        $privateUrl = $auth->privateDownloadUrl($baseUrl,$expires);

        return $privateUrl;
    }

    /**
     * get the qiniu Fop
     * @param  [string] $bucket [qiniu bucket]
     * @param  [string] $fop    [alias name]
     * @return [string]         [fop query string]
     */
    public static function getFop($bucket, $fop)
    {
        if (isset(self::$config['bucket'][$bucket]['fop'][$fop])) {
            return self::$config['bucket'][$bucket]['fop'][$fop];
        }

        return '';
    }

    /**
     * according bucket get the domain
     * @param  [string] $bucket [bucket name]
     * @return [string]
     */
    public static function getDomain($bucket)
    {
        if (isset(self::$config['bucket'][$bucket]['domain'])) {
            return self::$config['bucket'][$bucket]['domain'];
        }

        return current(self::$config['bucket'])['domain'];
    }

    /**
     * get bucket by given url
     * @param  [string] $url [file url]
     * @return [string]      [bucket name]
     */
    public static function getBucketByUrl($url)
    {
        $all_bucket = self::$config['bucket'];
        $first_bucket = current($all_bucket);
        foreach ($all_bucket as $bucket) {
            if (strpos($url, $bucket['name']) !== FALSE) {
                //find the bucket
                return $bucket['name'];
            }
        }

        //The first element is return by default
        return $first_bucket['name'];
    }


    /**
     * whether this bucket is public
     * @param  [string]  $bucket [bucket name]
     * @return [bool]
     */
    public static function isPublicBucket($bucket)
    {
        if (isset(self::$config['bucket'][$bucket]['public'])) {
            return self::$config['bucket'][$bucket]['public'];
        }

        return TRUE;
    }


    private static function makeBaseUrl($domain, $key){
        $keyEsc = rawurlencode($key);
        return "https://$domain/$keyEsc";
    }

}
