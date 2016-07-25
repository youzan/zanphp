<?php


namespace Zan\Framework\Network\Http\Security\Csrf;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestPostFilter;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Network\Http\Response\Cookie;
use Zan\Framework\Utilities\DesignPattern\Context;

class CsrfPostFilter Implements RequestPostFilter
{

    /**
     * @param Request $request
     * @param Response $response
     * @param Context $context
     * @return void
     */
    public function postFilter(Request $request, Response $response, Context $context)
    {
        if ($response instanceof \Zan\Framework\Network\Http\Response\Response) {
            /** @var CsrfToken $csrfToken */
            $csrfToken = $context->get('__zan_token');
            $response->withCookie(new Cookie('__zan_token', $csrfToken->getRaw(), time() + CsrfTokenManagerInterface::EXPIRE_TIME * 100));
        }
    }

}