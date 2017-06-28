<?php

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Sdk\Cdn\Qiniu;

class InitializeQiniuConfig
{
    public function bootstrap($server)
    {
        $config = Config::get('qiniu', []);
        if ($config) {
            Qiniu::setConfig($config);
        }
    }
}