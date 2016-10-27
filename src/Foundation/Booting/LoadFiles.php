<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/19
 * Time: 下午2:00
 */

namespace Zan\Framework\Foundation\Booting;


use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Loader;
use Zan\Framework\Foundation\Core\Path;

class LoadFiles implements Bootable
{
    public function bootstrap(Application $app)
    {
        $basePath = $app->getBasePath();
        $paths = [
            $basePath . '/vendor/zanphp/zan/src',
            $basePath . '/vendor/zanphp/nova/src',
        ];

        $excludeFiles = [
            Path::getRootPath() . "vendor/zanphp/zan/src/Foundation/View/Pages/Error.php",
        ];

        $loader = Loader::getInstance();
        foreach ($paths as $path) {
            $loader->load($path, $excludeFiles);
        }
    }
}