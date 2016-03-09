<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/8
 * Time: 上午11:11
 */
namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Sdk\Qiniu\Qiniu;

class Url{

    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';
    const SCHEME_FTP = 'ftp';

    private static $schemes = array(
        self::SCHEME_HTTP,
        self::SCHEME_HTTPS,
        self::SCHEME_FTP
    );

    private static $config;

    public static function setConfig(array &$config){
        self::$config = &$config;
    }

    /**
     * This method returns the base URI for your application, if the first
     * function argument is omitted the index.php will not be included in the
     * return value. If the second function argument is provided with
     * a string it will be prefixed to the return value as the scheme
     *
     * @param                $index
     * @param   string	     $scheme   url scheme
     * @return  string
     */
    public static function base($index = FALSE, $scheme = false)
    {
        if(false !== $scheme && !self::_checkScheme($scheme)){
            throw new InvalidArgumentException('Invalid scheme for Url');
        }
        $base_url = '/';
        $site_domain = '';
        $scheme = (false === $scheme) ? self::SCHEME_HTTP:$scheme;

        if( is_string($index) || strlen($index)) {
            $site_domain    = isset(self::$config[$index])?self::$config[$index]:null;
            if(empty(($site_domain))){
                $site_domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
                $site_domain = $scheme . '://' . $site_domain ;
            }
        }

        if (true === $index)
        {
            $site_domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $site_domain = $scheme . '://' . $site_domain ;
        }

        return rtrim($site_domain . $base_url, '/').'/';
    }

    /**
     * This method returns the absolute root URI of a given URI or, by
     * default, of your application's URI route.
     *
     * If url have scheme and host, the method will use itself's scheme and host.
     * else , the method where create scheme and host by the params -- index and scheme
     *
     * @param string $url
     * @param bool $index
     * @param string $scheme
     * @throws InvalidArgumentException
     */
    public static function site($url = '', $index = TRUE, $scheme = false)
    {
        if(false !== $scheme && !self::_checkScheme($scheme)){
            throw new InvalidArgumentException('Invalid scheme for Url::site');
        }

        $urlAnalysis = parse_url($url);

        $path = trim($urlAnalysis['path'], '/');

        if ($query = $urlAnalysis['query'])
        {
            $query = '?'.$query;
        }

        if ($fragment = $urlAnalysis['fragment'])
        {
            $fragment =  '#'.$fragment;
        }

        if($host = $urlAnalysis['host']){
            $scheme = $urlAnalysis['scheme'];
            if(!self::_checkScheme($scheme)){
                throw new InvalidArgumentException('Invalid url for Url::site');
            }
            $base_url = $scheme . '://' . $host . '/';
        }else{
            $base_url = URL::base($index, $scheme);
        }

        $url = $base_url.$path.$query.$fragment;

        return $url;
    }


    /**
     * This method returns cdn url.
     *
     * @param $url
     * @param $imgExt
     * @param $scheme
     * @param $removeImgExt
     * @return string
     * @throws InvalidArgumentException
     */
    public static function cdnSite($url , $imgExt , $scheme = false, $removeImgExt = false){
        if(false !== $scheme && !self::_checkScheme($scheme)){
            throw new InvalidArgumentException('Invalid scheme for Url::site');
        }

        if ($removeImgExt && ($pos = strrpos($url, '!'))) {
            $url = substr($url, 0, $pos);
        }

        //todo 设置配置文件
        $url = self::site((strlen($url) ? $url . $imgExt : 'upload_files/no_pic.png!280x280.jpg'), '', $scheme);

        if (!preg_match('~^(https?://static\.|static\.|dn-kdt-static\.qbox\.me|https?://dn-kdt-static\.qbox\.me)~s', $url)) {
            //如果是资源文件，则使用七牛生成地址
            $url = Qiniu::site($url);
        }

        return self::_convertWebp($url);
    }

    /**
     * check the scheme is valid
     *
     * @param $scheme
     * @return bool
     */
    private static function _checkScheme($scheme)
    {
        return in_array($scheme,self::$schemes);
    }

    /**
     * cdn url convert to webp
     *
     * @param $imgSrc
     * @param $canWebp
     * @return string
     */
    private static function _convertWebp($imgSrc, $canWebp = false){
        $multiple = 1;
        $pattern = '/\.([^.!]+)\!([0-9]{1,4})x([0-9]{1,4})(\+2x)?\.(.*)/';
        preg_match($pattern, $imgSrc, $matches);
        if ($matches && count($matches) >= 4) {
            if ('+2x' == $matches[4]) {
                $multiple = 2;
            }
            $extName = strtolower($matches[1]);
            $imgSrc = preg_replace($pattern, '.', $imgSrc) . $matches[1] . '?imageView2/2/w/' . (int)$matches[2] * $multiple . '/h/' . (int)$matches[3] * $multiple . '/q/75/format/' . ($canWebp ? ($extName == 'gif' ? 'gif' : 'webp') : $extName);
        }

        return $imgSrc;
    }

}