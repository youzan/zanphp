<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:00
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Network\Common\HttpClient;

class ServerRegister
{

    public function register()
    {
        $httpClient = new HttpClient($host, $port);
        yield $httpClient->post($uri, $params, $timeout);



    }


}