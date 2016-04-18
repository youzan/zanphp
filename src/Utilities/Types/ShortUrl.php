<?php
/**
 * Created by PhpStorm.
 * User: suqian
 * Date: 16/4/18
 * Time: 下午7:40
 */

namespace Zan\Framework\Utilities\Types;


use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Common\HttpClient;

class ShortUrl
{
    public static function get($url){
        if (!trim($url)){
            throw new InvalidArgumentException('链接地址错误');
        }
        $config = Config::get('shorturl');
        $response = (yield HttpClient::newInstance($config['host'],$config['port'])->get('/shorten?longUrl='.$url));
        $data = json_decode($response,true);
        if(!isset($data['status_code']) || !isset($data['data'])){
            yield '';
            return;
        }

        if(200 == $data['status_code'] && isset($data['data']) && isset($data['data']['url'])){
            yield $data['data']['url'];
            return;
        }
        yield '';
    }


}