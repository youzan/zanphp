<?php
namespace Zan\Framework\Contract\Network;

use Zan\Framework\Network\Connection\NovaClientPool;

interface LoadBalancingStrategyInterface
{
    public function get();
    public function initServers(NovaClientPool $pool);
}