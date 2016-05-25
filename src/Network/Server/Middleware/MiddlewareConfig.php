<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/13
 * Time: 下午4:49
 */

namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class MiddlewareConfig
{
    use Singleton;

    private $config = null;
    private $extendFilters = [];
    private $extendTerminators = [];

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setExtendFilters(array $extendFilters)
    {
        $this->extendFilters = $extendFilters;
    }

    public function setExtendTerminators(array $extendTerminators)
    {
        $this->extendTerminators = $extendTerminators;
    }

    public function getGroupValue(Request $request)
    {
        $route = $request->getRoute();
        $groupKey = null;

        for ($i = 0; ; $i++) {
            if (!isset($this->config['match'][$i])) {
                break;
            }
            $match = $this->config['match'][$i];
            $pattern = $match[0];
            if ($this->match($pattern, $route)) {
                $groupKey = $match[1];
                break;
            }
        }

        if (null === $groupKey) {
            return [];
        }
        if (!isset($this->config['group'][$groupKey])) {
            throw new InvalidArgumentException('Invalid Group name in MiddlewareManager');
        }

        return $this->config['group'][$groupKey];
    }

    public function match($pattern, $route)
    {
        if (preg_match($pattern, $route)) {
            return true;
        }
        return false;
    }


    public function addBaseFilters($filters)
    {
        $baseFilters = [

        ];
        return array_merge($baseFilters, $this->extendFilters, $filters);
    }

    public function addBaseTerminators($terminators)
    {
        $baseTerminators = [
        ];
        return array_merge($terminators, $this->extendTerminators, $baseTerminators);
    }
}