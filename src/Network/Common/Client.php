<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Network\Http\Client\HttpClient;
use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\Config;

class Client implements Async
{
    const JAVA_TYPE = 'java';
    const PHP_TYPE = 'php';

    /** @var  HttpClient */
    private $httpClient;

    /** @var  string */
    private $type;

    private function __construct($host, $port, $type)
    {
        $this->httpClient = new HttpClient($host, $port);
        $this->type = $type;
    }

    public static function call($api, $parameter = [], $method = 'POST')
    {
        $apiConfig = self::getApiConfig($api);

        $client = new self($apiConfig['host'], $apiConfig['port'], $apiConfig['type']);
        $client->setUri($api);
        $client->setMethod($method);
        $client->setTimeout($apiConfig['timeout']);
        $client->setParams($parameter);

        yield $client;
    }

    private function setUri($api)
    {
        if (false != strpos($api, '.')) {
             $this->httpClient->setUri('/' . str_replace('.', '/', $api));
        }
    }

    private function setMethod($method)
    {
        $this->httpClient->setMethod($method);
    }

    private function setTimeout($timeout)
    {
        $this->httpClient->setTimeout($timeout);
    }

    private function setParams($params)
    {
        if ($this->type == self::PHP_TYPE) {
            $params['debug'] = 'json';
        }

        $this->httpClient->setParams($params);
    }

    public function execute(callable $callback)
    {
        $this->httpClient->setCallback($this->getCallback($callback))->handle();
    }

    private function getCallback(callable $callback)
    {
        return function($response) use ($callback) {
            $data = isset($response['data']) ? $response['data'] : $response;
            call_user_func($callback, $data);
        };
    }

    private static function getApiConfig($api)
    {
        $javaApiConfig = Config::get('services.java');
        $phpApiConfig = Config::get('services.php');

        if (isset($javaApiConfig[$api])) {
            $javaApiConfig[$api]['type'] = self::JAVA_TYPE;
            return $javaApiConfig[$api];
        } else {
            $phpApiConfig['type'] = self::PHP_TYPE;
            return $phpApiConfig;
        }
    }
}