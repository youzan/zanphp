<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/14
 * Time: 11:47
 */

namespace Zan\Framework\Network\Http;


use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Utilities\DesignPattern\Context;

class Dispatcher {
    public function dispatch(Request $request, Context $context)
    {
        yield null;
    }
}