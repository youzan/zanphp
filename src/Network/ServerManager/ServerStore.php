<?php
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class ServerStore
{
    use Singleton;

    private $store = [];

    public function getServices($appName)
    {
        if (isset($this->store[$appName])) {
            return $this->store[$appName];
        } else {
            return null;
        }
    }

    public function setServices($appName, $servers)
    {
        $this->store[$appName] = $servers;
    }
}