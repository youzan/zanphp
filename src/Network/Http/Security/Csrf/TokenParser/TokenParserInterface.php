<?php


namespace Zan\Framework\Network\Http\Security\Csrf\TokenParser;


use Zan\Framework\Network\Http\Security\Csrf\CsrfToken;

interface TokenParserInterface
{

    public function generateToken($id, $tokenTime);

    /**
     * @param $tokenRaw
     * @return CsrfToken
     */
    public function parseToken($tokenRaw);

}