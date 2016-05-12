<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 16/4/12
 * Time: 12:14
 */

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Foundation\Core\ConfigLoader;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Store\Facade\Cache;

class InitializeCache
{
    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        $cacheMap = ConfigLoader::getInstance()->load(Path::getCachePath());
        Cache::init($cacheMap);
    }
}