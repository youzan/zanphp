<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/10
 * Time: 17:32
 */

namespace Zan\Framework\Foundation\Exception;


use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Exception\Handler\ExceptionLogger;

class ExceptionHandlerChain
{
    protected $handlerChain = [
        ExceptionLogger::class,
    ];
    protected $handlerMap = [];
    
    public function __construct()
    {
        $this->clearHandlers();
    }
    
    public function clearHandlers()
    {
        $this->handlerChain = [];
        $this->handlerMap = [];
    }

    public function handle(\Exception $e)
    {
        if(empty($this->handlerChain)){
            throw $e;
        }

        //at less one handler handle the exception
        //else throw the exception out
        $exceptionHandled = false;
        foreach ($this->handlerChain as $handler){
            $status = $handler->handle($e);
            if(true === $status){
                $exceptionHandled = true;
            }
        }
        
        if(false === $exceptionHandled){
            throw $e;
        }
    }
    
    public function addHandler(ExceptionHandler $handler)
    {
        $hash = spl_object_hash($handler);
        if(isset($this->handlerMap[$hash])){
            return false;
        }

        $this->handlerMap[$hash]= true;
        $this->handlerChain[] = $handler;
    }
    
    public function addHandlerByName($handlerName)
    {
        if(isset($this->handlerMap[$handlerName])){
            return false;
        }

        $this->handlerMap[$handlerName]= true;
        $this->handlerChain[] = new $handlerName();
    }
    
    public function addHandlersByName(array $handlers)
    {
        if(!$handlers){
            return false;
        }    
        
        foreach ($handlers as $handlerName){
            $this->addHandlerByName($handlerName);
        }
    }
    
}