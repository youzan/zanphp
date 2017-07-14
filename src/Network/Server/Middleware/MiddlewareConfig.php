<?php

namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class MiddlewareConfig
{
    use Singleton;

    private $config = null;
    private $exceptionHandlerConfig = [];
    private $zanFilters = [];
    private $zanTerminators = [];

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setExceptionHandlerConfig(array $exceptionHandlerConfig)
    {
        $this->exceptionHandlerConfig = $exceptionHandlerConfig;
    }

    public function getExceptionHandlerConfig()
    {
        return $this->exceptionHandlerConfig;
    }

    public function setZanFilters(array $zanFilters)
    {
        $this->zanFilters = $zanFilters;
    }

    public function setZanTerminators(array $zanTerminators)
    {
        $this->zanTerminators = $zanTerminators;
    }

    public function getGroupValue(Request $request, $config)
    {
        $isTcpGenericRequest = $request instanceof \Zan\Framework\Network\Tcp\Request && $request->isGenericInvoke();
        if ($isTcpGenericRequest) {
            $genericRoute = $request->getGenericRoute();
        }

        $route = $request->getRoute();
        $groupKey = null;

        for ($i = 0; ; $i++) {
            if (!isset($config['match'][$i])) {
                break;
            }
            $match = $config['match'][$i];
            $pattern = $this->setDelimit($match[0]);
            if ($this->match($pattern, $route)) {
                $groupKey = $match[1];
                break;
            }

            if (!empty($genericRoute) && $this->match($pattern, $genericRoute)) {
                $groupKey = $match[1];
                break;
            }
        }

        if (null === $groupKey) {
            return [];
        }
        if (!isset($config['group'][$groupKey])) {
            throw new InvalidArgumentException('Invalid Group name in MiddlewareManager, see: http://zanphpdoc.zanphp.io/libs/middleware/filters.html#tcp');
        }

        return $config['group'][$groupKey];
    }

    public function getRequestFilters($request)
    {
        return $this->getGroupValue($request, $this->config);
    }

    public function addExceptionHandlers($request, $filter)
    {
        $exceptionHandlers = $this->getGroupValue($request, $this->exceptionHandlerConfig);
        return array_merge($filter, $exceptionHandlers);
    }

    public function match($pattern, $route)
    {
        if (preg_match($pattern, $route)) {
            return true;
        }
        return false;
    }

    private function setDelimit($pattern)
    {
        return '#' . $pattern . '#i';
    }

    public function addBaseFilters($filters)
    {
        $baseFilters = [
            RpcContextFilter::class,
            TraceFilter::class,
            DebuggerTraceFilter::class,
            ServiceChainFilter::class,
        ];
        return array_merge($baseFilters, $this->zanFilters, $filters);
    }

    public function addBaseTerminators($terminators)
    {
        $baseTerminators = [
            AsyncTaskTerminator::class,

            WorkerTerminator::class,
            DbTerminator::class,
            CacheTerminator::class,
            DebuggerTraceTerminator::class,
            TraceTerminator::class,
        ];
        return array_merge($terminators, $this->zanTerminators, $baseTerminators);
    }
}
