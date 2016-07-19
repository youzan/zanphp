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
        $body = $response->getBody();

        $jsonData = json_decode($body, true);
        $result = $jsonData ? $jsonData : $body;

        if(!isset($result['status_code']) || 200 != $result['status_code']){
            yield '';
            return;
        }
        yield $result['data']['url'];
    }


}