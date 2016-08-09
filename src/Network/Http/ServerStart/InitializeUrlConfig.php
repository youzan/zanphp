<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/5/31
 * Time: 上午11:28
 */

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Utilities\Types\URL;
use Zan\Framework\Foundation\Core\Config;

class InitializeUrlConfig
{
    public function bootstrap($server)
    {
        $config = Config::get('url');
        if (!$config) {
            return;
        }
        URL::setConfig($config);
    }
}
