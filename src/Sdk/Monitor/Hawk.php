<?php

namespace Zan\Framework\Sdk\Monitor;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\HttpClient;


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

    const SUCCESS = 1001;
    const APPLICATION_PREFIX = 'php_soa_';

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
     *      'work_id' => 2,
     *      'host' => 'bc_sdfs',
     *  ],
     * ),
     * @param $biz
     * @param array $metrics
     * @param array $tags
     */
    public function add($biz, array $metrics, array $tags)
    {
        $tags['application'] = self::APPLICATION_PREFIX . $this->application;
        $tags['host'] = gethostname();
        $metricsArr = [];
        foreach ($metrics as $k => $v) {
            $metricsArr[] = [
                "metric" => $k,
                "value" => $v
            ];
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

        $resp = (yield $this->httpClient->post($this->config['uri'], $this->data));
        $result = $resp->getResponseJson();

        $this->data = [];

        if (!isset($result['code']) || $result['code'] != self::SUCCESS) {
            //TODO: 上报失败
            var_dump("hawk上报失败");
        }
    }
}