<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Network\Http\Client\HttpClient;
use Zan\Framework\Foundation\Contract\Async;

class Client implements Async
{
    const JAVA_TYPE = 'java';
    const PHP_TYPE = 'php';

    /** @var  HttpClient */
    private $httpClient;

    private $type;

    private $host;
    private $port;

    private $timeout;

    private $uri;
    private $method;

    private $params;


    private function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public static function call($api, $params = [], $method = 'POST')
    {
        $apiConfig = self::getApiConfig($api);

        $params = self::filterParams($params, $apiConfig['type']);

        $client = new self($apiConfig['host'], $apiConfig['port']);
        $client->setType($apiConfig['type']);
        $client->setTimeout($apiConfig['timeout']);
        $client->setMethod($method);
        $client->setUri($api);
        $client->setParams($params);

        yield $client->build();
    }

    public function execute(callable $callback)
    {
        $this->httpClient->setCallback($this->getCallback($callback))->handle();
    }

    private function setType($type)
    {
        $this->type = $type;
    }

    private function setMethod($method)
    {
        $this->method = $method;
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

    private function build()
    {
        $this->httpClient = new HttpClient($this->host, $this->port);

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
                'content_type' => $contentType
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
            call_user_func($callback, $response);
        };
    }

    private static function getApiConfig($api)
    {
        $apiConfig = require_once(__DIR__ . '/ApiConfig.php');

        if (isset($apiConfig['java'][$api])) {
            $apiConfig['java'][$api]['type'] = self::JAVA_TYPE;
            return $apiConfig['java'][$api];
        } else {
            $apiConfig['php']['type'] = self::PHP_TYPE;
            return $apiConfig['php'];
        }
    }

    private static function filterParams($params, $type)
    {
        if ($type == self::PHP_TYPE) {
            $params['debug'] = 'json';
        }

        return $params;
    }
}