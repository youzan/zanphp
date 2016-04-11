<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/9
 * Time: 12:06
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\ExceptionHandlerChain;
use Zan\Framework\Network\Http\Exception\Handler\PageNotFoundHandler;
use Zan\Framework\Network\Http\Exception\Handler\RedirectHandler;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class RequestExceptionHandlerChain extends ExceptionHandlerChain 
{
    use Singleton;
    
    private $handles = [
        RedirectHandler::class,
        PageNotFoundHandler::class,
    ];

    public function init()
    {   
        $this->addHandlersByName($this->handles);
    }
}