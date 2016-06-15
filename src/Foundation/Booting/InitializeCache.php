<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 16/4/12
 * Time: 12:14
 */

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\ConfigLoader;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Store\Facade\Cache;

class InitializeCache implements Bootable
{
    public function bootstrap(Application $app)
    {
        $cacheMap = ConfigLoader::getInstance()->load(Path::getCachePath());
        Cache::initConfigMap($cacheMap);
    }
}