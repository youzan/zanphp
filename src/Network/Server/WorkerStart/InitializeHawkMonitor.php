<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/4/26
 * Time: 下午2:08
 */

namespace Zan\Framework\Network\Server\WorkerStart;

use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Sdk\Monitor\Hawk;

class InitializeHawkMonitor implements Bootable
{
    public function bootstrap($server)
    {
        Hawk::getInstance()->run($server);
    }

}