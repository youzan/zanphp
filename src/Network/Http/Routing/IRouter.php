<?php
namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Network\Http\Request\Request;

interface IRouter
{
    /*
     * @return array ['module', 'controller', 'action']
     */
    public function dispatch(Request $request);
}
