<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:01
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class LoadBalancingManager
{
    use Singleton;

    private $config;

    private $strategyMap = [
        'Polling' => 'Zan\Framework\Network\ServerManage\LoadBalancingStrategy\Polling',
    ];

    private function setConfig($config)
    {
        $this->config = $config;
    }

    public function work()
    {

    }

    public function offline()
    {

    }

    public function addOnline()
    {

    }

    public function get()
    {

    }

    private function getStrategy()
    {

    }
}