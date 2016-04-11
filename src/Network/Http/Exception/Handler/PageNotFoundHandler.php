<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/11
 * Time: 11:00
 */

namespace Zan\Framework\Network\Http\Exception\Handler;


use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Network\Http\Exception\PageNotFoundException;

class PageNotFoundHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        if(!is_a($e, PageNotFoundException::class)){
            return false;
        }
    }
}