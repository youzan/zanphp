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

class LoadFiles implements Bootable
{
    public function bootstrap(Application $app)
    {
        $basePath = $app->getBasePath();
        $paths = [
            $basePath . '/vendor/zanphp',
            $basePath . '/src',
        ];

        $loader = Loader::getInstance();
        foreach ($paths as $path) {
            $loader->load($path);
        }
    }
}