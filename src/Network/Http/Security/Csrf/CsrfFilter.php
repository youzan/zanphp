<?php


namespace Zan\Framework\Network\Http\Security\Csrf;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Exception\SystemException;
use Zan\Framework\Network\Http\Security\Csrf\Exception\TokenException;
use Zan\Framework\Utilities\DesignPattern\Context;

class CsrfFilter Implements RequestFilter
{
    const TOKEN_NAME = 'csrf_token';
    const TOKEN_HEADER_NAME = 'X-ZAN-TOKEN';
    const DEFAULT_COOKIE_EXPIRE_TIME = 3600;

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
         */
        /** @var \Zan\Framework\Network\Http\Request\Request $request */
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
                if (empty($tokenRaw)) {
                    throw new TokenException('Invalid token');
                } else {
                    $token = $this->csrfTokenManager->parseToken($tokenRaw);
                    $modules = $this->getModules($request);
                    if ($this->csrfTokenManager->isTokenValid($modules, $token)) {
                        $newToken = $this->csrfTokenManager->refreshToken($token);
                    } else {
                        throw new TokenException('Token expired');
                    }
                }
            }
            //yield (cookieSet(self::TOKEN_NAME, $newToken->getRaw(), static::DEFAULT_COOKIE_EXPIRE_TIME));
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

}