<?php


namespace Zan\Framework\Network\Http\Security\Csrf;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\SystemException;
use Zan\Framework\Network\Http\Response\Response;
use Zan\Framework\Network\Http\Security\Csrf\Exception\TokenException;
use Zan\Framework\Utilities\DesignPattern\Context;

class CsrfFilter Implements RequestFilter
{
    const TOKEN_NAME = 'csrf_token';
    const TOKEN_HEADER_NAME = 'X-ZAN-TOKEN';

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    public function __construct()
    {
        $this->csrfTokenManager = Di::make(CsrfTokenManager::class);
    }

    /**
     * @param Request $request
     * @param Context $context
     * @return \Zan\Framework\Contract\Network\Response
     * @throws TokenException
     */
    public function doFilter(Request $request, Context $context)
    {
        /**
         * FIXME
         * We should never care about the instance of interface
         *
         * @var \Zan\Framework\Network\Http\Request\Request $request
         */
        if ($request instanceof \Zan\Framework\Network\Http\Request\Request) {
            $tokenRaw = $this->getTokenRaw($request);
            if ($this->isReading($request)) {
                if (empty($tokenRaw)) {
                    $newToken = $this->csrfTokenManager->createToken();
                } else {
                    $token = $this->csrfTokenManager->parseToken($tokenRaw);
                    $newToken = $this->csrfTokenManager->refreshToken($token);
                }
            } else {
                if($this->isWhite($request)){
                   yield null;
                   return;
                }
                if (empty($tokenRaw)) {
                    throw new TokenException('Invalid token', Response::HTTP_FORBIDDEN);
                } else {
                    $token = $this->csrfTokenManager->parseToken($tokenRaw);
                    $modules = $this->getModules($request);
                    if ($this->csrfTokenManager->isTokenValid($modules, $token)) {
                        $newToken = $this->csrfTokenManager->refreshToken($token);
                    } else {
                        throw new TokenException('Token expired', Response::HTTP_FORBIDDEN);
                    }
                }
            }

            /**
             * @see \Zan\Framework\Foundation\View\JsVar::setCsrfToken
             * @see \Zan\Framework\Foundation\Domain\HttpController::display
             * @see \Zan\Framework\Foundation\Domain\HttpController::render
             * @fixme 当前的CSRF Token实现非常耦合我们的js前端(和IRON兼容), 留给后人改进了...
             */
            yield (setContext(self::TOKEN_NAME, $newToken->getRaw()));
        }
    }

    private function getTokenRaw(\Zan\Framework\Network\Http\Request\Request $request)
    {
        $token = $request->parameter(self::TOKEN_NAME, null) ?: $request->header(self::TOKEN_HEADER_NAME, null);
        return $token;
    }

    private function isReading(\Zan\Framework\Network\Http\Request\Request $request)
    {
        return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
    }

    private function getModules(Request $request)
    {
        static $store = [];
        $route = $request->getRoute();
        if (!isset($store[$route])) {
            $routeAry = explode('/', $route);
            if (2 > count($routeAry)) {
                throw new SystemException('Error route');
            }
            if (!isset($routeAry[2])) {
                $routeAry[2] = 'index';
            }

            $result['module'] = $routeAry[0];
            $result['controller'] = $routeAry[1];
            $result['action'] = $routeAry[2];
            $store[$route] = $result;
        }
        return $store[$route];
    }

    private function isWhite(Request $request){
        $whiteList = Config::get('secure.csrf.whitelist');
        $whiteList = $whiteList ? $whiteList : [];

        if(empty($whiteList)){
            return false;
        }
        $module = $this->getModules($request);
        if(empty($module)){
            return false;
        }
        $tmp = [$module['module'],$module['controller'],$module['action']];
        $decision = '';
        foreach($tmp as $r){
            $decision .= $decision ? '.'.$r : $r;
            if(isset($whiteList[$decision])){
                return true;
            }
        }
        return false;
    }

}