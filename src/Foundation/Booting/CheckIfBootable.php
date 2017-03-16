<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/16
 * Time: 下午6:07
 */

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;

class CheckIfBootable implements Bootable
{
    public function bootstrap(Application $app)
    {
        $requiredExtensions = ["apcu", "lz4", "swoole"];
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                sys_error("$extension is not loaded, use php -m to check php modules");
                exit(1);
            }
        }
        $targetVersion = "2.1.0";
        $currentVersion = swoole_version();
        if ($currentVersion < $targetVersion) {
            sys_error("Your swoole version($currentVersion) is lower than $targetVersion, ".
                "please upgrade your swoole extension");
            exit(1);
        }
    }
}