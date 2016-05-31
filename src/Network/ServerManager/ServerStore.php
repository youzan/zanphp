<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/25
 * Time: 上午11:52
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class ServerStore
{
    use Singleton;

    private function getKey($key)
    {
        return 'server_' . $key;
    }

    public function set($key, $value)
    {
        if (apcu_exists($this->getKey($key))) {
            return apcu_store($this->getKey($key), json_encode($value));
        } else {
            return apcu_add($this->getKey($key), json_encode($value));
        }
    }

    public function get($key)
    {
        $data = apcu_fetch($this->getKey($key));
        if ('' != $data) {
            return json_decode($data, true);
        }
        return null;
    }
}