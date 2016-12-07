<?php


namespace Zan\Framework\Network\Http\Security\Csrf\Factory;


use Zan\Framework\Network\Http\Security\Csrf\CsrfToken;

interface TokenFactoryInterface
{

    /**
     * @param $id
     * @param $tokenTime
     * @return CsrfToken
     */
    public function buildToken($id, $tokenTime);

    /**
     * @param $tokenRaw
     * @return CsrfToken
     */
    public function buildFromRawText($tokenRaw);

}