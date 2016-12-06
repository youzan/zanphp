<?php

namespace Zan\Framework\Sdk\Monitor;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Env;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\Types\Json;
use Zan\Framework\Utilities\Types\Time;


/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/3
 * Time: 上午11:44
 */
class Hawk
{
    use Singleton;

    private $data = [];
    /**
     * @var array
     * {
     *  'server': {
     *      'service': {
     *          'method': {
     *              'ip' {
     *              }
     *          }
     *      }
     *  }
     * }
     */
    private $serviceData = [];

    private $application;
    private $config;
    private $httpClient;
    private $server;
    private $ip;
    private $port;
    private $host;
    private $isRunning = false;

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

    public function __construct()
    {
        $this->config = Config::get('hawk');
        $this->application = Application::getInstance()->getName();
        $this->httpClient = new HttpClient($this->config['host'], $this->config['port']);
        $this->host = Env::get('hostname');
        $this->ip = Env::get('ip');
        $this->port = Config::get('server.port');
    }

    public function run($server)
    {
        if ($this->config['run'] == false) {
            return;
        }

        $this->isRunning = true;
        $this->server = $server;
        Timer::tick($this->config['time'], [$this, 'report']);
    }

    public function report()
    {

        // 计算soa相关值
        foreach ($this->serviceData as $side => $v) {
            foreach ($v as $service => $vv) {
                foreach ($vv as $method => $vvv) {
                    foreach ($vvv as $ip => $kv) {
                        $metrics = $tags = [];

                        $kv[self::TOTAL_SUCCESS_COUNT] = isset($kv[self::TOTAL_SUCCESS_COUNT]) ?$kv[self::TOTAL_SUCCESS_COUNT]: 0;
                        $kv[self::TOTAL_SUCCESS_TIME] = isset($kv[self::TOTAL_SUCCESS_TIME]) ?$kv[self::TOTAL_SUCCESS_TIME]: 0;
                        $kv[self::TOTAL_FAILURE_TIME] = isset($kv[self::TOTAL_FAILURE_TIME]) ?$kv[self::TOTAL_FAILURE_TIME]: 0;
                        $kv[self::TOTAL_FAILURE_COUNT] = isset($kv[self::TOTAL_FAILURE_COUNT]) ?$kv[self::TOTAL_FAILURE_COUNT]: 0;
                        $kv[self::MAX_FAILURE_TIME] = isset($kv[self::MAX_FAILURE_TIME]) ?$kv[self::MAX_FAILURE_TIME]: 0;
                        $kv[self::LIMIT_COUNT] = isset($kv[self::LIMIT_COUNT]) ?self::LIMIT_COUNT: 0;
                        $kv[self::TOTAL_CONCURRENCY] = isset($kv[self::TOTAL_CONCURRENCY]) ?$kv[self::TOTAL_CONCURRENCY]: 0;
                        $kv[self::CONCURRENCY_COUNT] = isset($kv[self::CONCURRENCY_COUNT]) ?$kv[self::CONCURRENCY_COUNT]: 0;
                        $kv[self::MAX_SUCCESS_TIME] = isset($kv[self::MAX_SUCCESS_TIME]) ?$kv[self::MAX_SUCCESS_TIME]: 0;

                        // 成功次数
                        $metrics['success'] = $kv[self::TOTAL_SUCCESS_COUNT];
                        // 平均成功耗时
                        $metrics['avg.elapsed'] = $metrics['avg.success.elapsed'] = $kv[self::TOTAL_SUCCESS_COUNT] == 0 ? 0 : floor($kv[self::TOTAL_SUCCESS_TIME] / $kv[self::TOTAL_SUCCESS_COUNT]);
                        // 最大成功耗时
                        $metrics['max.success.elapsed'] = $kv[self::MAX_SUCCESS_TIME];
                        // 失败次数
                        $metrics['failure'] = $kv[self::TOTAL_FAILURE_COUNT];
                        // 失败平均耗时
                        $metrics['avg.failure.elapsed'] = $kv[self::TOTAL_FAILURE_COUNT] == 0 ? 0 : floor($kv[self::TOTAL_FAILURE_TIME] / $kv[self::TOTAL_FAILURE_COUNT]);
                        // 最大失败耗时
                        $metrics['max.failure.elapsed'] = $kv[self::MAX_FAILURE_TIME];
                        // 限流
                        $metrics['reject'] = $kv[self::LIMIT_COUNT];
                        // 平均并发数
                        $metrics['concurrent'] = $kv[self::CONCURRENCY_COUNT] == 0 ? 0 : floor($kv[self::TOTAL_CONCURRENCY] / $kv[self::CONCURRENCY_COUNT]);

                        $tags['method'] = $method;
                        $tags['service'] = $service;
                        $tags['side'] = $side;
                        if ($side == 'server') {
                            $tags['server'] = $this->ip . ':' . $this->port;
                            $tags['client'] = $ip;
                        } else {
                            $tags['client'] = $this->ip;
                            $tags['server'] = $ip;
                        }

                        $this->add('youzan.soa', $metrics, $tags);
                    }
                }
            }
        }

        // send
        $courotine = $this->send();
        Task::execute($courotine);
        // clean
        $this->clear();
    }

    public function addServerServiceData($service, $method, $clientIp, $key, $val)
    {
        $clientIp = $this->long2ip($clientIp);
        $this->serviceData['server'][$service][$method][$clientIp][$key] = $val;
    }

    public function addClientServiceData($service, $method, $serverIp, $key, $val)
    {
        $serverIp = $this->long2ip($serverIp);
        $this->serviceData['client'][$service][$method][$serverIp][$key] = $val;
    }

    /**
     * array(
     *  'business' => 'worker_memory',
     *  'timestamp' => 1415250938,
     *  'metrics' => [
     *      'used' => 234234234,
     *  ],
     *  'tags' => [
     *      'application' => 'pf-web',
     *      'work_id' => '2',
     *      'host' => 'bc_sdfs',
     *  ],
     * ),
     * @param $biz
     * @param array $metrics
     * @param array $tags
     */
    public function add($biz, array $metrics, array $tags = [])
    {
        $tags['application'] = $this->application;
        $tags['host'] = $this->host;
        $tags['worker_id'] = (string)$this->server->swooleServer->worker_id;
        $metricsArr = [];
        foreach ($metrics as $k => $v) {
            $metricsArr[$k] = $v;
        }

        $this->data[] = [
            'business' => $biz,
            'timestamp' => Time::stamp(),
            'metrics' => $metricsArr,
            'tags' => $tags
        ];
    }
    
    public function send()
    {
        try {
            $response = (yield $this->httpClient->postJson(self::URI, $this->data));
        } catch (\Exception $e) {
            var_dump('hawk上报失败');
            return;
        }
        $statusCode = -1;

        if ($response) {
            $statusCode = $response->getStatusCode();
        }

        if ($statusCode != self::SUCCESS_CODE) {
            //TODO: 上报失败
            var_dump("hawk上报失败");
        }
    }

    private function clear()
    {
        $this->data = $this->serviceData = [];
    }

    public function addTotalSuccessTime($side, $service, $method, $ip, $diffSec)
    {
        $ip = $this->long2ip($ip);
        $diffUSec = $diffSec * 1000000;
        if ($side == 'server') {
            $this->serviceData['server'][$service][$method][$ip][self::TOTAL_SUCCESS_TIME] =
                isset($this->serviceData['server'][$service][$method][$ip][self::TOTAL_SUCCESS_TIME])
                    ? $this->serviceData['server'][$service][$method][$ip][self::TOTAL_SUCCESS_TIME]
                    : 0;
                $this->serviceData['server'][$service][$method][$ip][self::TOTAL_SUCCESS_TIME] += $diffUSec;

            $this->serviceData['server'][$service][$method][$ip][self::MAX_SUCCESS_TIME] =
                isset($this->serviceData['server'][$service][$method][$ip][self::MAX_SUCCESS_TIME])
                    ? $this->serviceData['server'][$service][$method][$ip][self::MAX_SUCCESS_TIME] : 0;

            if ($this->serviceData['server'][$service][$method][$ip][self::MAX_SUCCESS_TIME] < $diffUSec) {
                $this->serviceData['server'][$service][$method][$ip][self::MAX_SUCCESS_TIME] = $diffUSec;
            }
        } else {
            $this->serviceData['client'][$service][$method][$ip][self::TOTAL_SUCCESS_TIME] = isset($this->serviceData['client'][$service][$method][$ip][self::TOTAL_SUCCESS_TIME])
                    ? $this->serviceData['client'][$service][$method][$ip][self::TOTAL_SUCCESS_TIME] : 0;
            $this->serviceData['client'][$service][$method][$ip][self::TOTAL_SUCCESS_TIME] += $diffSec * 1000000;

            $this->serviceData['client'][$service][$method][$ip][self::MAX_SUCCESS_TIME] =
                isset($this->serviceData['client'][$service][$method][$ip][self::MAX_SUCCESS_TIME])
                    ? $this->serviceData['client'][$service][$method][$ip][self::MAX_SUCCESS_TIME] : 0;

            if ($this->serviceData['client'][$service][$method][$ip][self::MAX_SUCCESS_TIME] < $diffUSec) {
                $this->serviceData['client'][$service][$method][$ip][self::MAX_SUCCESS_TIME] = $diffUSec;
            }
        }
    }

    public function addTotalFailureTime($side, $service, $method, $ip, $diffSec)
    {
        $ip = $this->long2ip($ip);
        $diffUSec = $diffSec * 1000000;
        if ($side == 'server') {
            $this->serviceData['server'][$service][$method][$ip][self::TOTAL_FAILURE_TIME] =
                isset($this->serviceData['server'][$service][$method][$ip][self::TOTAL_FAILURE_TIME])
                ? $this->serviceData['server'][$service][$method][$ip][self::TOTAL_FAILURE_TIME] : 0;
            $this->serviceData['server'][$service][$method][$ip][self::TOTAL_FAILURE_TIME] += $diffUSec;

            $this->serviceData['server'][$service][$method][$ip][self::MAX_FAILURE_TIME] =
                isset($this->serviceData['server'][$service][$method][$ip][self::MAX_FAILURE_TIME])
                ? $this->serviceData['server'][$service][$method][$ip][self::MAX_FAILURE_TIME] : 0;

            if ($this->serviceData['server'][$service][$method][$ip][self::MAX_FAILURE_TIME] < $diffUSec) {
                $this->serviceData['server'][$service][$method][$ip][self::MAX_FAILURE_TIME] = $diffUSec;
            }
        } else {
            $this->serviceData['client'][$service][$method][$ip][self::TOTAL_FAILURE_TIME] =
                isset($this->serviceData['client'][$service][$method][$ip][self::TOTAL_FAILURE_TIME])
                ? $this->serviceData['client'][$service][$method][$ip][self::TOTAL_FAILURE_TIME] : 0;
            $this->serviceData['client'][$service][$method][$ip][self::TOTAL_FAILURE_TIME] += $diffUSec;

            $this->serviceData['client'][$service][$method][$ip][self::MAX_FAILURE_TIME] =
                isset($this->serviceData['client'][$service][$method][$ip][self::MAX_FAILURE_TIME])
                ? $this->serviceData['client'][$service][$method][$ip][self::MAX_FAILURE_TIME] : 0;

            if ($this->serviceData['client'][$service][$method][$ip][self::MAX_FAILURE_TIME] < $diffUSec) {
                $this->serviceData['client'][$service][$method][$ip][self::MAX_FAILURE_TIME] = $diffUSec;
            }
        }
    }

    public function addTotalSuccessCount($side, $service, $method, $ip)
    {
        $ip = $this->long2ip($ip);
        if ($side == 'server') {
            $this->serviceData['server'][$service][$method][$ip][self::TOTAL_SUCCESS_COUNT] =
                isset($this->serviceData['server'][$service][$method][$ip][self::TOTAL_SUCCESS_COUNT]) ?
                    $this->serviceData['server'][$service][$method][$ip][self::TOTAL_SUCCESS_COUNT] : 0;
            $this->serviceData['server'][$service][$method][$ip][self::TOTAL_SUCCESS_COUNT] += 1;
        } else {
            $this->serviceData['client'][$service][$method][$ip][self::TOTAL_SUCCESS_COUNT] =
                isset($this->serviceData['client'][$service][$method][$ip][self::TOTAL_SUCCESS_COUNT])
                ? $this->serviceData['client'][$service][$method][$ip][self::TOTAL_SUCCESS_COUNT] : 0;
            $this->serviceData['client'][$service][$method][$ip][self::TOTAL_SUCCESS_COUNT] += 1;
        }
    }

    public function addTotalFailureCount($side, $service, $method, $ip)
    {
        $ip = $this->long2ip($ip);
        if ($side == 'server') {
            $this->serviceData['server'][$service][$method][$ip][self::TOTAL_FAILURE_COUNT] =
                isset($this->serviceData['server'][$service][$method][$ip][self::TOTAL_FAILURE_COUNT])
                    ? $this->serviceData['server'][$service][$method][$ip][self::TOTAL_FAILURE_COUNT] : 0;
            $this->serviceData['server'][$service][$method][$ip][self::TOTAL_FAILURE_COUNT] += 1;
        } else {
            $this->serviceData['client'][$service][$method][$ip][self::TOTAL_FAILURE_COUNT] =
                isset($this->serviceData['client'][$service][$method][$ip][self::TOTAL_FAILURE_COUNT])
                ? $this->serviceData['client'][$service][$method][$ip][self::TOTAL_FAILURE_COUNT] : 0;
            $this->serviceData['client'][$service][$method][$ip][self::TOTAL_FAILURE_COUNT] += 1;
        }
    }

    private function long2ip($ip) {
        return is_numeric($ip) ? long2ip($ip) : $ip;
    }

}