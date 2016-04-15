<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/12
 * Time: 14:20
 */

namespace Zan\Framework\Network\Tcp;


use Zan\Framework\Foundation\Exception\ExceptionHandlerChain;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class RequestExceptionHandlerChain extends ExceptionHandlerChain
{
    use Singleton;
    
    public function handle(\Exception $e)
    {
        
    }
}