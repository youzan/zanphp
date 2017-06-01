<?php
/**
 * Created by PhpStorm.
 * User: suqian
 * Date: 17/6/1
 * Time: 上午10:22
 */

namespace Zan\Framework\Utilities\Types;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Common\HttpClient;

class HtmlSanitize
{

    public  function xss($content,array $filters = []) {
        if (!trim($content)){
            throw new InvalidArgumentException('二维码内容不能为空');
        }

        $params = [
            'element' => Arr::get($filters,'element',1), //=1 输出所有属性
            'attribute' => Arr::get($filters,'attribute',2), // =1输出全部 attribute, =2输出白名单的
            'style_property' => Arr::get($filters,'style_property',1),
            'style_property_value' => Arr::get($filters,'style_property_value',1),
            'url_protocol' => Arr::get($filters,'url_protocol',1),
            'url_domain' => Arr::get($filters,'url_domain',0),
            'iframe_url_protocol' => Arr::get($filters,'iframe_url_protocol',1),
            'iframe_url_domain' => Arr::get($filters,'iframe_url_domain',1),
        ];
        $config = Config::get('html_sanitize');
        $response = (yield HttpClient::newInstance($config['host'],$config['port'])->post('/sanitize',$params));
        $body = $response->getBody();
        yield $body;
    }

}