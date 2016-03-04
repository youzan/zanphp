<?php

namespace Zan\Framework\Network\Http;

class Client
{
    /**
     * @var HttpClient
     */
    private static $httpClient;

    public static function call($path, $parameter = [], callable $callback = null, $method = 'POST')
    {
        yield (new HttpClient($path, $parameter, $method));
    }

}