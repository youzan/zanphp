<?php

namespace Zan\Framework\Sdk\Monitor;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\Types\Arr;

class Hawk
{
    use Singleton;

    private $isRunning = false;
    /**
     * @var Hawker
     */
    private $hawkerImpl;

    const SUCCESS_CODE = 200;
    const URI = '/report';

    const TOTAL_SUCCESS_TIME = 'totalSuccessTime';
    const TOTAL_SUCCESS_COUNT = 'totalSuccessCount';
    const MAX_SUCCESS_TIME = 'maxSuccessTime';
    const TOTAL_FAILURE_TIME = 'totalFailureTime';
    const TOTAL_FAILURE_COUNT = 'totalFailureCount';
    const MAX_FAILURE_TIME = 'maxFailureTime';
    const LIMIT_COUNT = 'limitCount';
    const TOTAL_CONCURRENCY = 'totalConcurrency';
    const CONCURRENCY_COUNT = 'concurrencyCount';

    const CLIENT = 'client';
    const SERVER = 'server';

    public function run($server)
    {
        $config = Config::get('hawk');
        if ($config['run'] == false) {
            return;
        }

        $zanHawkConfig = Config::get('zan_hawk', []);
        if (!empty($zanHawkConfig)) {
            $config = Arr::merge($zanHawkConfig, $config);
        }

        if (isset($config['hawk_class'])) {
            $hawkerClass = $config['hawk_class'];
            if (is_subclass_of($hawkerClass, Hawker::class)) {
                $this->isRunning = true;
                $this->hawkerImpl = new $hawkerClass($server);
                Timer::tick($config['time'], [$this, 'report']);
                return;
            } else {
                throw new ZanException("$hawkerClass should be an Implementation of Hawker");
            }
        }
    }

    public function add($biz, array $metrics, array $tags = [])
    {
        if ($this->isRunning == false) {
            return;
        }

        $this->hawkerImpl->add($biz, $metrics, $tags);
    }

    public function report()
    {
        if ($this->isRunning == false) {
            return;
        }

        $this->hawkerImpl->report();
    }

    public function addServerServiceData($service, $method, $clientIp, $key, $val)
    {
        if ($this->isRunning == false) {
            return;
        }

        if (method_exists($this->hawkerImpl, "addServerServiceData")) {
            $this->hawkerImpl->addServerServiceData($service, $method, $clientIp, $key, $val);
        }
    }

    public function addClientServiceData($service, $method, $serverIp, $key, $val)
    {
        if ($this->isRunning == false) {
            return;
        }

        if (method_exists($this->hawkerImpl, "addClientServiceData")) {
            $this->hawkerImpl->addClientServiceData($service, $method, $serverIp, $key, $val);
        }
    }

    public function addTotalSuccessTime($side, $service, $method, $ip, $diffSec)
    {
        if ($this->isRunning == false) {
            return;
        }

        $this->hawkerImpl->addTotalSuccessTime($side, $service, $method, $ip, $diffSec);
    }

    public function addTotalFailureTime($side, $service, $method, $ip, $diffSec)
    {
        if ($this->isRunning == false) {
            return;
        }

        $this->hawkerImpl->addTotalFailureTime($side, $service, $method, $ip, $diffSec);
    }

    public function addTotalSuccessCount($side, $service, $method, $ip)
    {
        if ($this->isRunning == false) {
            return;
        }

        $this->hawkerImpl->addTotalSuccessCount($side, $service, $method, $ip);
    }

    public function addTotalFailureCount($side, $service, $method, $ip)
    {
        if ($this->isRunning == false) {
            return;
        }

        $this->hawkerImpl->addTotalFailureCount($side, $service, $method, $ip);
    }

}