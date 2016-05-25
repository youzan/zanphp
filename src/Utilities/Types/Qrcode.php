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

class Qrcode {
    public static function create($data,$size='200x200',$base64=false, $styles = []) {
        $size = strtolower($size);
        if (strpos($size, 'x') !== false) {
            $tmp = explode('x', $size);
            $size = $tmp[0];
        }
        return self::newImpl($data, $size, $base64, $styles);
    }


    private static function newImpl($content, $size = '200', $base64 = false, $styles = []) {
        if (!trim($content)){
            throw new InvalidArgumentException('二维码内容不能为空');
        }

        $params = [
            'txt' => $content,
            'size' => $size,
            'margin' => isset($styles['margin']) ? $styles['margin'] : 20,
            'level' => isset($styles['level']) ? $styles['level'] : 0,
            'hint' => isset($styles['hint']) ? $styles['hint'] : 2,
            'case' => isset($styles['case']) ? $styles['case'] : 1,
            'ver' => isset($styles['ver']) ? $styles['ver'] : 1,
            'fg_color' => isset($styles['fg_color']) ? $styles['fg_color'] : '000000',
            'bg_color' => isset($styles['bg_color']) ? $styles['bg_color'] : 'ffffff',
        ];
        $config = Config::get('qrcode');
        $response = (yield HttpClient::newInstance($config['host'],$config['port'])->get('/',$params));
        if ($response) {
            if (false === $base64){
                yield $response;
            } else {
                yield 'data:image/png;base64,' . base64_encode($response);
            }
            return;
        }
        yield '';
    }
}