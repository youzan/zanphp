<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/13
 * Time: 20:47
 */

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Foundation\Core\ConfigLoader;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Foundation\Application;

class MiddlewareManager
{

    use Singleton;

    private $config = null;
    private $conKey = 'middleware';

    public function loadConfig($path = '')
    {
        $this->config = empty($path) ? Config::get($this->conKey) : ConfigLoader::getInstance()->load($path, true);
        $this->config['match'] = isset($this->config['match']) ? $this->config['match'] : [];
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
        $filters = $this->getGroupValue($request);
        foreach ($filters as $filter) {
            $filterObjectName = $this->getObject($filter);
            $filterObject = new $filterObjectName();
            if ($filterObject instanceof RequestFilter) {
                $response = (yield $filterObject->doFilter($request, $context));
                if (null !== $response) {
                    yield $response;
                    return;
                }
            }
            unset($filterObject);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Context $context
     * @return \Generator
     */
    public function executeTerminators(Request $request, Response $response, Context $context)
    {
        $terminators = $this->getGroupValue($request);
        foreach ($terminators as $terminator) {
            $terminatorObjectName = $this->getObject($terminator);
            $terminatorObject = new $terminatorObjectName();
            if ($terminatorObject instanceof RequestTerminator) {
                yield $terminatorObject->terminate($request, $response, $context);
            }
            unset($terminatorObject);
        }
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
            return null;
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

    private function getObject($objectName)
    {
        return $objectName;
    }
}
