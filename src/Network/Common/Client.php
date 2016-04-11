<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Network\Http\Client\HttpClient;
use Zan\Framework\Foundation\Contract\Async;

class Client implements Async
{
    /** @var  HttpClient */
    private $httpClient;

    public static function call($api, $parameter = [], $method = 'POST')
    {
        $apiConfig = ClientRouter::lookup($api);

        $client = new self($apiConfig['host'], $apiConfig['port']);
        $client->setApi($api);
        $client->setMethod($method);
        $client->setTimeout($apiConfig['timeout']);
        $client->setParams($parameter);

        yield $client;
    }

    private function __construct($host, $port)
    {
        $this->httpClient = new HttpClient($host, $port);
    }

    private function setApi($api)
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
        $params['debug'] = 'json';
        $this->httpClient->setParams($params);
    }

    public function execute(Callable $callback)
    {
        $this->httpClient->setCallback($callback)->handle();
    }
}