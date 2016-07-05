<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/7/5
 * Time: 下午4:55
 */
namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Network\Connection\ConnectionManager;

class DbTerminator implements RequestTerminator
{
    public function terminate(Request $request, Response $response, Context $context)
    {
        
    }
}