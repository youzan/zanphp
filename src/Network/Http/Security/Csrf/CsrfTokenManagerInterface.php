<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 7/23/16
 * Time: 05:16
 */

namespace Zan\Framework\Network\Http\Security\Csrf;


interface CsrfTokenManagerInterface
{

    /**
     * @var int
     */
    const EXPIRE_TIME = 60;

    /**
     * @param $id
     * @param $tokenTime
     * @return CsrfToken
     */
    public function createToken();

    /**
     * @param $tokenRaw
     * @return CsrfToken
     */
    public function parseToken($tokenRaw);

    /**
     * @param CsrfToken $token
     * @return mixed
     */
    public function refreshToken(CsrfToken $token);

    /**
     * @param CsrfToken $token
     * @return bool
     */
    public function isTokenValid(CsrfToken $token);

//    public function removeToken($tokenId);

}