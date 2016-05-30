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
            yield apcu_store($this->getKey(), json_encode($value));
        } else {
            yield apcu_add($this->getKey(), json_encode($value));
        }
    }

    public function get($key)
    {
        $data = (yield apcu_fetch($this->getKey()));
        if ('' != $data) {
            yield json_decode($data, true);
        }
        yield [];
    }
}