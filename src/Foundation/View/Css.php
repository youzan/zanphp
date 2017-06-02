<?php

namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Utilities\Types\Time;
use Zan\Framework\Utilities\Types\URL;

class Css extends BaseLoader
{
    public function load($index, $vendor = false)
    {
        $url = $this->getCssURL($index, $vendor);
        echo '<link rel="stylesheet" href="' . $url . '" onerror="_cdnFallback(this)">';
    }

    public function getCssURL($index, $vendor = false)
    {
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
