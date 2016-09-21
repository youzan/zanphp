<?php

namespace Zan\Framework\Sdk\Monitor;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Utilities\Types\Json;


/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/3
 * Time: 上午11:44
 */
class Hawk
{
    private $data;
    private $application;
    private $config;

    private $httpClient;

    const SUCCESS_CODE = 200;
    const URI = '/report';

    public function __construct()
    {
        $this->config = Config::get('hawk');
        $this->application = Application::getInstance()->getName();
        $this->httpClient = new HttpClient($this->config['host'], $this->config['port']);
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
    public function add($biz, array $metrics, array $tags)
    {
        $tags['application'] = $this->application;
        $tags['host'] = gethostname();
        $metricsArr = [];
        foreach ($metrics as $k => $v) {
            $metricsArr[$k] = $v;
        }

        $this->data[] = [
            'business' => $biz,
            'timestamp' => time(),
            'metrics' => $metricsArr,
            'tags' => $tags
        ];
    }
    
    public function send()
    {
        if ($this->config['run'] == false) {
            return;
        }

        try {
            $response = (yield $this->httpClient->postJson(self::URI, $this->data));
        } catch (\Exception $e) {
            var_dump('hawk上报失败');
        }
        $statusCode = -1;

        if ($response) {
            $statusCode = $response->getStatusCode();
        }

        $this->data = [];

        if ($statusCode != self::SUCCESS_CODE) {
            //TODO: 上报失败
            var_dump("hawk上报失败");
        }
    }
}