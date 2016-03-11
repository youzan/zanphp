<?php

/**
 * @author hupp
 * create date: 16/03/02
 */

namespace Zan\Framework\Network\Http;

class Client
{
    public static function call($path, $parameter = [], $method = 'POST')
    {
        yield (new HttpClient($path, $parameter, $method));
    }

}