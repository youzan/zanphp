<?php


namespace Zan\Framework\Network\Http\Security\Csrf;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestPostFilter;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Network\Http\Response\Cookie;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\Helper\FilterHelper;

class CsrfPostFilter Implements RequestPostFilter
{

    use FilterHelper;

    /**
     * @param Request $request
     * @param Response $response
     * @param Context $context
     * @return void
     */
    public function postFilter(Request $request, Response $response, Context $context)
    {
        if ($response instanceof \Zan\Framework\Network\Http\Response\Response) {
            $csrfTokenManager = Di::make(CsrfTokenManager::class);

            /** @var CsrfToken $csrfToken */
            $csrfToken = $context->get('__zan_token');
            $modules = $this->getModules($request);
            $response->withCookie(new Cookie('__zan_token', $csrfToken->getRaw(), time() + $csrfTokenManager->getTimeToLive($modules)));
        }
    }

}