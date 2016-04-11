<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/9
 * Time: 12:06
 */

namespace Zan\Framework\Network\Http;


use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class RequestExceptionHandler implements ExceptionHandler
{
    use Singleton;
    private $handlerFilters = [];
    private $handlerMap = [];

    public function handle(\Exception $e)
    {
        
    }

    public function addHandler(ExceptionHandler $handler)
    {
        $hash = spl_object_hash($handler);
        if(isset($this->handlerMap[$hash])){
            return false;
        }
        
        $this->handlerMap[$hash]= true;
        $this->handlerFilters[] = $handler;
    }
}