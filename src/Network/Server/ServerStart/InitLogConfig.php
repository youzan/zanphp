<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/31
 * Time: 上午11:19
 */

namespace Zan\Framework\Network\Server\ServerStart;

use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Sdk\Log\Log;

class InitLogConfig implements Bootable
{

    public function bootstrap($server)
    {
        $configArray = $this->readConfig();
        $this->initLog($configArray);
    }

    private function readConfig()
    {
        return Config::get('log');
    }

    private function initLog($configArray)
    {
        foreach ($configArray as $key => $config) {
            Log::getInstance($key);
        }
    }

}
