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

class Css extends BaseLoader
{
    public function load($index, $vendor = false)
    {
        $url = $this->getCssUrl($index, $vendor);
        echo '<link rel="stylesheet" href="' . $url. '" onerror="_cdnFallback(this)">';
    }

    public function getCssUrl($index, $vendor = false)
    {
        return 'static.youzan.com/' . $index;
        $isUseCdn = Config::get('js.use_css_cdn');
        $url = '';
        if ($vendor !== false) {
            $url = URL::site($index, $isUseCdn ? $this->getCdnType() : 'static');
        } else {
            $arr = explode('.', $index, 2);

            if ($isUseCdn) {
                $url = URL::site(Config::get($index), $this->getCdnType());
            } else {
                $url = URL::site('local_css/' . $arr[1] . '.css?t=' . Time::current(TRUE), 'static');
            }
        }
        return $url;
    }
}