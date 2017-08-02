<?php
namespace Zan\Framework\Network\Server\WorkerStart;


use Zan\Framework\Foundation\Core\RunMode;
use ZanPHP\Container\Container;
use ZanPHP\Contracts\ServiceChain\ServiceChainer;

class InitializeServiceChain
{
    /**
     * @param $server
     * @param $workerId
     */
    public function bootstrap($server, $workerId)
    {
        // make & initialize discovering serviceChain
        if (RunMode::get() !== "online") {
            $container = Container::getInstance();
            if ($container->has(ServiceChainer::class)) {
                $container->make(ServiceChainer::class);
            }
        }
    }
}