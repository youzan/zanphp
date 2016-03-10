<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/8
 * Time: 下午7:44
 */
namespace Zan\Framework\Sdk;

use Zan\Framework\Network\Http\HttpClient;

class ShortUrl{

    private static $config;

    public static function setConfig(array $config){
        self::$config = $config;
    }

    public static function get($url,$failReturnPre=false)
    {
        if(!$url) return '';

        //todo 短链服务地址暂时写死
        $request_url = 'http://kdt.im/shorten?longUrl=' . urlencode($url);

        $option = [
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT => 1
        ];

        $httpClinet = new HttpClient($request_url,$option);
        $httpClinet->execute($callBack);

        if(!$data){
            return self::returnFailUrl($url,$failReturnPre);
        }

        $data = json_decode($data,true);
        if(!isset($data['status_code']) || !isset($data['data'])){
            return self::returnFailUrl($url,$failReturnPre);
        }

        if(200 == $data['status_code'] && isset($data['data']) && isset($data['data']['url'])){
            return $data['data']['url'];
        }

        return self::returnFailUrl($url,$failReturnPre);
    }

    private static function returnFailUrl($url,$failReturnPre=false)
    {
        if(false === $failReturnPre){
            return '';
        }
        return $url;
    }



}