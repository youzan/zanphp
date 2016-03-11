<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/1
 * Time: 17:55
 */

namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\View\BaseLoader;


class JsLoader extends BaseLoader
{
    public function loadJS($index, $vendor=false, $async=false, $crossorigin=false)
    {
        $url = $this->getJsUrl($index,$vendor);

        echo "<script src=\"${url}\" onerror=\"_cdnFallback(this)\"";
        if($async) {
            echo ' async ';
        }
        if ($crossorigin) {
            echo ' crossorigin="anonymous"';
        }
        echo "></script>";

        return TRUE;
    }

    public function getJsUrl($index,$vendor=false)
    {
        $isUseCdn = Config::get('js.use_js_cdn');
        $url = '';
        $project = '';

        if ($vendor !== false) {
            $url = URL::site($index, $isUseCdn ? $this->getCdnType() : 'static');
        } else {
            $arr = explode('.', $index, 2);

            if ($isUseCdn) {
                $url = URL::site(Config::get($index), $this->getCdnType());
            } else {
                $project = substr($arr[0], 8);
                $url = URL::site($project .'/'. $arr[1] . '/main.js', 'static');
            }
        }

        return $url;
    }

    public function load($path)
    {
        echo 'load js';
    }
}