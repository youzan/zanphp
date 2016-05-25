<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
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
        if(!isset($response['status_code']) || 200 != $response['status_code']){
            yield '';
            return;
        }
        yield $response['data']['url'];
    }


}