<?php
namespace Zan\Framework\Network\Connection\LoadBalancingStrategy;

use Zan\Framework\Contract\Network\LoadBalancingStrategyInterface;
use Zan\Framework\Network\Connection\NovaClientPool;

class Polling implements LoadBalancingStrategyInterface
{

    const NAME = "polling";
    const MAX_GET_RETRY = 5;

    /**
     * @var NovaClientPool
     */
    private $connectionPool;

    /**上次选择的服务器*/
    private $currentIndex = -1;
    /**当前调度的权值*/
    private $currentWeight = 0;
    /**最大权重*/
    private $maxWeight;
    /**权重的最大公约数*/
    private $gcdWeight;
    /**服务器数*/
    private $serverCount;
    private $servers;

    public function __construct(NovaClientPool $connectionPool)
    {
        $this->initServers($connectionPool);
    }

    public function init()
    {
        $configs = $this->connectionPool->getConfig();
        $this->servers = [];
        foreach($configs as $k => $v) {
            $this->servers[] = $v;
        }
        $this->serverCount = count($this->servers);
        $this->maxWeight = $this->greatestWeight();
        $this->gcdWeight = $this->greatestCommonDivisor();
        $this->currentIndex = -1;
        $this->currentWeight = 0;
    }

    public function initServers(NovaClientPool $connectionPool) {
        $this->connectionPool = $connectionPool;
        $this->init();
    }

    /**
     * 得到两值的最大公约数
     */
    public function greaterCommonDivisor($a, $b){
        if($a % $b == 0){
            return $b;
        }else{
            return $this->greaterCommonDivisor($b,$a % $b);
        }
    }
    /**
     * 得到list中所有权重的最大公约数，实际上是两两取最大公约数d，然后得到的d
     * 与下一个权重取最大公约数，直至遍历完
     */
    public function greatestCommonDivisor(){
        $divisor = 0;
        for($index = 0, $len = $this->serverCount; $index < $len - 1; $index++){
            if($index == 0){
                $divisor = $this->greaterCommonDivisor(
                    $this->servers[$index]['weight'], $this->servers[$index+1]['weight']);
            }else{
                $divisor = $this->greaterCommonDivisor($divisor, $this->servers[$index]['weight']);
            }
        }
        return $divisor;
    }

    /**
     * 得到list中的最大的权重
     */
    public function greatestWeight(){
        $weight = 0;
        foreach($this->servers as $server){
            if($weight < $server['weight']){
                $weight = $server['weight'];
            }
        }
        return $weight;
    }

    public function get($timeout = 100)
    {
        $retryInterval = intval(min(100, max(10, ceil($timeout / static::MAX_GET_RETRY))));
        yield $this->getWithRetry($retryInterval, static::MAX_GET_RETRY);
    }

    private function getWithRetry($retryInterval, $retry)
    {
        if ($retry > 0) {
            $connection = null;

            if (intval($this->serverCount) > 0) {
                $connection = $this->algorithm();
            }

            if ($connection === null) {
                yield taskSleep($retryInterval);
                yield $this->getWithRetry($retryInterval, --$retry);
            } else {
                yield $connection;
            }
        } else {
            yield null;
        }
    }

    /**
     *  算法流程：
     *  假设有一组服务器 S = {S0, S1, …, Sn-1}
     *  有相应的权重，变量currentIndex表示上次选择的服务器
     *  权值currentWeight初始化为0，currentIndex初始化为-1 ，当第一次的时候返回 权值取最大的那个服务器，
     *  通过权重的不断递减 寻找 适合的服务器返回
     */
    private function algorithm()
    {
        while (true) {
            $this->currentIndex = ($this->currentIndex + 1) % $this->serverCount;
            if ($this->currentIndex == 0) {
                $this->currentWeight = $this->currentWeight - $this->gcdWeight;
                if ($this->currentWeight <= 0) {
                    $this->currentWeight = $this->maxWeight;
                    if ($this->currentWeight == 0) {
                        return null;
                    }
                }
            }

            if ($this->servers[$this->currentIndex]['weight'] >= $this->currentWeight) {
                $server = $this->servers[$this->currentIndex];
                return $this->connectionPool->getConnectionByHostPort($server['host'], $server['port']);
            }
        }
    }
}