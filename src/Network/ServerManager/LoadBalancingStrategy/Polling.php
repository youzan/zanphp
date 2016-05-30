<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/27
 * Time: 下午7:15
 */
namespace Zan\Framework\Network\ServerManager\LoadBalancingStrategy;

use Zan\Framework\Contract\Network\LoadBalancingStrategyInterface;

class Polling implements LoadBalancingStrategyInterface
{
    private $connecitonPool;

    public function setConnectionPool($connectionPool)
    {
        $this->connecitonPool = $connectionPool;
    }

    public function get()
    {

    }

    public function algorithm()
    {

    }
}