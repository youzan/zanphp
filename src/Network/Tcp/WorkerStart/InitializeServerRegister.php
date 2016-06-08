<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/6/8
 * Time: 上午11:19
 */

namespace Zan\Framework\Network\Tcp\WorkerStart;

use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Network\ServerManager\ServerRegisterInitiator;

class InitializeServerRegister implements Bootable
{
    /**
     * @param
     */
    public function bootstrap($server)
    {
        ServerRegisterInitiator::getInstance()->init();
    }
}