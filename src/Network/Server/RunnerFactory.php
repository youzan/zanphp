<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/13
 * Time: 20:19
 */
namespace Zan\Framework\Network\Server;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class RunnerFactory {
    use Singleton;

    /**
     * @param $controllerName
     * @param Request $request
     * @param Context $context
     * @return \Zan\Framework\Foundation\Domain\HttpController
     */
    public function makeController($controllerName, Request $request, Context $context)
    {
        return null;
    }

    /**
     * @param $serviceName
     * @param Request $request
     * @param Context $context
     * @return \Zan\Framework\Foundation\Domain\Service
     */
    public function makeService($serviceName, Request $request, Context $context)
    {
    }


}