<?php


namespace Zan\Framework\Network\Http\Security\Csrf;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Network\Http\Security\Csrf\Exception\TokenException;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\Helper\FilterHelper;

class CsrfFilter Implements RequestFilter
{
    use FilterHelper;

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
                    if ($this->csrfTokenManager->isTokenValid($token, $modules)) {
                        $newToken = $this->csrfTokenManager->refreshToken($token);
                    } else {
                        throw new TokenException('Token expired');
                    }
                }
            }
            $context->set('__zan_token', $newToken);
        }
    }

    private function getTokenRaw(\Zan\Framework\Network\Http\Request\Request $request)
    {
        $token = $request->cookie('__zan_token', null) ?: $request->header('X-ZAN-TOKEN', null);
        return $token;
    }

    private function isReading(\Zan\Framework\Network\Http\Request\Request $request)
    {
        return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
    }

}