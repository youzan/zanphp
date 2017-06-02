<?php

namespace Zan\Framework\Network\Http\Security\Csrf;


interface CsrfTokenManagerInterface
{

    /**
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
     * @param array $modules
     * @param CsrfToken $token
     * @return bool
     */
    public function isTokenValid(array $modules, CsrfToken $token = null);


    /**
     * Return expire time in seconds
     *
     * @param array $modules
     * @return int
     */
    public function getTTL(array $modules);

}