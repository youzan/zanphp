<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 00:36
 */

namespace Zan\Framework\Network\Tcp;


use Zan\Framework\Foundation\Application;
use Zan\Framework\Utilities\DesignPattern\Context;
use Kdt\Iron\Nova\Nova;

class Dispatcher {
    /**
     * @var Request
     */
    private $request = null;
    private $context = null;

    public function dispatch(Request $request, Context $context)
    {
        $this->request = $request;
        $this->context = $context;

        yield $this->runService();
    }

    private function runService()
    {
        $serviceName = $this->getServiceName();

        $service = new $serviceName();

        if ($this->request->isGenericInvoke()) {
            $method = $this->request->getGenericMethodName();
        } else {
            $method = $this->request->getMethodName();
        }

        $args    = $this->request->getArgs();
        $args    = is_array($args) ? $args : [$args];

        yield call_user_func_array([$service,$method],$args);
    }

    private function getServiceName()
    {
        $app = Application::getInstance();
        $appNamespace = $app->getNamespace();
        $appName = $app->getName();

        if ($this->request->isGenericInvoke()) {
            $servieName = $this->request->getGenericServiceName();
        } else {
            $servieName = $this->request->getNovaServiceName();
        }

        $servieName = str_replace('.', '\\', $servieName);
        $servieName = Nova::removeNovaNamespace($servieName, $appName);
        $servieName = $appNamespace . $servieName;

        return $servieName;
    }
}