<?php

namespace Zan\Framework\Network\Http;

class Client
{
    /**
     * @var HttpClient
     */
    private static $httpClient;

    public static function call($path, $parameter = [], callable $callback = null, $method = 'GET')
    {
        self::$httpClient = new HttpClient($path, $parameter, $method);

        return self::$httpClient->call($callback);
    }

}