<?php

namespace Zan\Framework\Network\Server\ServerStart;

use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Sdk\Log\Log;

class InitLogConfig implements Bootable
{

    public function bootstrap($server)
    {
        $configArray = Config::get('log');
        if (!$configArray) {
            return true;
        }

        $this->initLog($configArray);
    }

    private function initLog($configArray)
    {
        foreach ($configArray as $key => $config) {
            Log::make($key);
        }
    }

}
