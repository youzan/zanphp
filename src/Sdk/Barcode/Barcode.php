<?php

namespace Zan\Framework\Sdk\Barcode;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Network\Common\Response;
use Zan\Framework\Sdk\Barcode\Exception\BadRequestException;
use Zan\Framework\Sdk\Barcode\Exception\ServiceUnavailableException;

/**
 * Class Barcode
 * @package Zan\Framework\Sdk\Barcode
 * @see http://doc.qima-inc.com/pages/viewpage.action?pageId=8815007
 */
class Barcode {

    public static function create($text, $height = 10, $styles = [], $barcode = 20) {
        yield self::generate($text, $height, $styles, $barcode);
    }

    public static function createDataUrl($text, $height = 10, $styles = [], $barcode = 20)
    {
        yield self::generate($text, $height, $styles, $barcode, true);
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

        if (isset($styles['hrt'])) {
            $params['hrt'] = $styles['hrt'];
        }

        $config = Config::get('services.barcode');
        if (!isset($config['host']) || !isset($config['port'])) {
            throw new InvalidArgumentException('条形码服务配置为空');
        }
        $timeout = isset($config['timeout']) ? $config['timeout'] : 1500;

        /** @var Response $response */
        $response = (yield HttpClient::newInstance($config['host'], $config['port'])->get('/barcode', $params, $timeout));
        $statusCode = $response->getStatusCode();
        if (200 != $statusCode) {
            if (400 == $statusCode) {
                throw new BadRequestException('调用条形码服务参数错误');
            } else {
                throw new ServiceUnavailableException('条形码服务错误');
            }
        }

        $body = $response->getBody();
        if ($body) {
            if (false === $base64) {
                yield $body;
            } else {
                yield 'data:image/png;base64,' . base64_encode($body);
            }
        } else {
            yield '';
        }
    }
}