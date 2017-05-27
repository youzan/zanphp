<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/27
 * Time: 下午7:17
 */
namespace Zan\Framework\Contract\Network;

use Zan\Framework\Network\Connection\NovaClientPool;

interface LoadBalancingStrategyInterface
{
    public function get($timeout = 50);
    public function initServers(NovaClientPool $pool);
}