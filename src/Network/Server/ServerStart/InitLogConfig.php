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
        $configArray = Config::get('log');
        if(!$configArray){
            return true;
        }
        
        $this->initLog($configArray);
    }

    private function initLog($configArray)
    {
        foreach ($configArray as $key => $config) {
            Log::getInstance($key);
        }
    }

}
