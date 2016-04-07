<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/16
 * Time: 21:26
 */

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Path;

class InitializePathes implements Bootable
{
    public function bootstrap(Application $app)
    {
        $rootPath = $app->getBasePath();

        Path::init($rootPath);
    }
}