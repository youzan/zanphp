<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Foundation\Exception\SystemException;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Common\HttpClient as HClient;
use Zan\Framework\Foundation\Contract\Async;

class Client implements Async
{
    const JAVA_TYPE = 'java';
    const PHP_TYPE = 'php';

    private static $apiConfig;

    /** @var  HttpClient */
    private $httpClient;

    private $type;

    private $host;
    private $port;

    private $timeout;

    private $uri;
    private $method;

    private $params;

    private $format = 'yar';


    private function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public static function call($api, $params = [],$callback = null, $method = 'POST',$format='yar')
    {
        $apiConfig = self::getApiConfig($api);
        $params = self::filterParams($params, $apiConfig['type']);
        $client = new self($apiConfig['host'], $apiConfig['port']);
        $client->setType($apiConfig['type']);
        $client->setTimeout($apiConfig['timeout']);
        $client->setMethod($method);
        $client->setUri($api);
        $client->setParams($params);
        $client->setFormat($format);

        yield $client->build();
    }

    public function execute(callable $callback, $task)
    {
        $this->httpClient->setCallback($this->getCallback($callback))->handle();
    }

    private function setType($type)
    {
        $this->type = $type == 'local' ? self::PHP_TYPE : $type;
    }

    private function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    private function setUri($api)
    {
        if (false !== strpos($api, '.')) {
            $this->uri = '/' . str_replace('.', '/', $api);
        }
    }

    private function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    private function setParams($params)
    {
       $this->params = $params;
    }

    private function setFormat($format){
        $this->format = $format;
    }
    private function build()
    {
        $this->httpClient = new HClient($this->host, $this->port);

        $this->httpClient->setTimeout($this->timeout);
        $this->httpClient->setMethod($this->method);

        if ($this->method != 'POST' and $this->method != 'PUT') {
            $this->uri = $this->uri . '?' . http_build_query($this->params);
        } else {
            if ($this->type == self::PHP_TYPE) {
                $body = http_build_query($this->params);
                $contentType = 'application/x-www-form-urlencoded';
            } else {
                $body = json_encode($this->params);
                $contentType = 'application/json';
            }
            $this->httpClient->setHeader([
                'Content-Type' => $contentType
            ]);
            $this->httpClient->setBody($body);
        }
        $this->httpClient->setUri($this->uri);

        return $this;
    }

    private function getCallback(callable $callback)
    {
        return function($response) use ($callback) {
            $jsonData = json_decode($response, true);
            $response = $jsonData ? $jsonData : $response;
            if($this->format =='yar' && $this->type == self::PHP_TYPE && isset($response['code'])){
                if($response['code']){
                    $msg = $response['msg'] ? $response['msg'] : $response['data'];
                    throw new ZanException($msg,$response['code']);
                }
                $response = $response['data'];
            }
            call_user_func($callback, $response);
        };
    }

    private static function getApiConfig($api)
    {
        if (is_null(self::$apiConfig)) {
            $configFile  = Application::getInstance()->getBasePath() . '/vendor/zan-config/iron/files/service_host.php';
            if (file_exists($configFile)) {
                $allApiConfig = require $configFile;
            } else {
                $configFile = __DIR__ . '/ApiConfig.php';
                if (!file_exists($configFile)) {
                    throw new SystemException('service_host 配置文件不存');
                }
                $allApiConfig = require $configFile;
            }

            $runMode = RunMode::get();
            self::$apiConfig = isset($allApiConfig[$runMode]) ? $allApiConfig[$runMode] : $allApiConfig['test'];
        }

        $pos = stripos ($api, ".");
        if (false === $pos) {
            return false;
        }
        $mod = substr ($api, 0, $pos);
        $target = isset (self::$apiConfig[$mod]) ? self::$apiConfig[$mod] : ['type' => 'local'];
        if (isset($target['sub']) && $target['sub']) {
            $target = static::getSubTarget($target, $api);
        }
        if (!empty($target['host'])) {
            $target['host'] = str_replace('http://', '', $target['host']);
            $hostInfo = explode(':', $target['host']);
        } else {
            $hostInfo = null;
        }

        $host = isset($hostInfo[0]) ? str_replace('/', '', $hostInfo[0]) : 'api.koudaitong.com';
        $port = isset($hostInfo[1]) ? $hostInfo[1] : 80;
        $type = isset($target['type']) ? $target['type'] : 'local';
        $timeout = isset($target['timeout']) ? $target['timeout'] : 3;

        return [
            'host' => $host,
            'port' => $port,
            'timeout' => $timeout,
            'type' => $type
        ];

    }

    private static function getSubTarget($target, $path) {
        $sub = $target ['sub'];
        while(true) {
            foreach ( $sub as $item ) {
                if ($item ['mod'] == $path) {
                    return $item;
                }
            }
            $cursor = strrpos ( $path, "." );
            if (!$cursor) {
                break;
            }
            $path = substr ( $path, 0, $cursor );
        }
        return $target;
    }

    private static function filterParams($params, $type)
    {
        if ($type == 'local') {
            $params['debug'] = 'json';
        }

        return $params;
    }
}