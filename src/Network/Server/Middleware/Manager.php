<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/13
 * Time: 20:47
 */

namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class Manager {
    use Singleton;

    private $path = null;
    public function loadConfig($path)
    {
        $this->path = $path;
    }

    public function optimize()
    {

    }

    /**
     * @param Request $request
     * @param Context $context
     * @return \Generator
     */
    public function executeFilters(Request $request, Context $context)
    {

    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Context $context
     * @return \Generator
     */
    public function executeTerminators(Request $request, Response $response, Context $context)
    {

    }
}