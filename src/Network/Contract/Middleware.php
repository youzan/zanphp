<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/7
 * Time: 17:42
 */

namespace Zan\Framework\Network\Contract;


use Zan\Framework\Utilities\DesignPattern\Context;

class Middleware {
    public function handle(Request $request, Context $context=null)
    {
        return null;
    }

    public function terminate(Request $request, Response $response, Context $context=null)
    {
        return null;
    }
}