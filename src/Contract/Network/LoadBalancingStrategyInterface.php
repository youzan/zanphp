<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/27
 * Time: 下午7:17
 */
namespace Zan\Framework\Contract\Network;

interface LoadBalancingStrategyInterface
{
    public function get();

    public function algorithm();
}