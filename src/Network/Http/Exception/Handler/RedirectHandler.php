<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/9
 * Time: 12:09
 */

namespace Zan\Framework\Network\Http\Exception\Handler;


use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Network\Http\Exception\RedirectException;

class RedirectHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        if(!isset($e->redirectUrl) && !is_a($e, RedirectException::class)){
            return null;
        }
        
        //todo return redirect response
    }
}