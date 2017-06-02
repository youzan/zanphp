<?php

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Utilities\Types\URL;
use Zan\Framework\Foundation\Core\Config;

class InitializeUrlConfig
{
    public function bootstrap($server)
    {
        $config = Config::get('url', []);
        URL::setConfig($config);
    }
}