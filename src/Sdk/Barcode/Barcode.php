<?php

namespace Zan\Framework\Sdk\Barcode;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Common\HttpClient;

/**
 * Class Barcode
 * @package Zan\Framework\Sdk\Barcode
 * @see http://doc.qima-inc.com/pages/viewpage.action?pageId=8815007
 */
class Barcode {

    public static function create($text, $height = 10, $styles = [], $barcode = 20) {
        return self::generate($text, $height, $styles, $barcode);
    }

    public static function createDataUrl($text, $height = 10, $styles = [], $barcode = 20)
    {
        return self::generate($text, $height, $styles, $barcode, true);
    }

    private static function generate($text, $height = 10, $styles = [], $barcode = 20, $base64 = false) {
        if (!trim($text)){
            throw new InvalidArgumentException('条形码内容不能为空');
        }

        $params = [
            'txt' => $text,
            'height' => intval($height),
            'rotate' => isset($styles['rotate']) ? $styles['rotate'] : 0,
            'scale' => isset($styles['scale']) ? $styles['scale'] : 1,
            'barcode' => $barcode,

            // TODO 过滤不合法的颜色值
            'bg' => isset($styles['bg']) ? $styles['bg'] : 'ffffff',
            'fg' => isset($styles['fg']) ? $styles['fg'] : '000000',
        ];

        $config = Config::get('services.barcode');
        if (!isset($config['host']) || !isset($config['port'])) {
            throw new InvalidArgumentException('条形码服务配置为空');
        }

        // TODO 1. 判断接口返回的状态码
        // TODO 2. timeout
        $resp = (yield HttpClient::newInstance($config['host'], $config['port'])->get('/', $params));
        $response = $resp->getResponseJson();

        if ($response) {
            if (false === $base64) {
                yield $response;
            } else {
                yield 'data:image/png;base64,' . base64_encode($response);
            }
            return;
        }
        yield '';
    }
}