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
                sys_error("\033[1;31m$extension is not loaded, use php -m to check php modules, to install extension:
                curl -s http://gitlab.qima-inc.com/xiaohengjin/php7Env/raw/master/install_extensions.sh|sh\033[0m");
            }
        }
        $targetVersion = "2.1.5";
        $currentVersion = swoole_version();
        if ($currentVersion < $targetVersion) {
            sys_error("\033[1;31mYour swoole version($currentVersion) is lower than $targetVersion, ".
                "please upgrade your swoole extension, to upgrade:
                curl -s http://gitlab.qima-inc.com/xiaohengjin/php7Env/raw/master/swoole_upgrade.sh|sh\033[0m");
        }
    }
}