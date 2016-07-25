<?php


namespace Zan\Framework\Network\Http\Security\Csrf\TokenParser;


use Zan\Framework\Network\Http\Security\Csrf\CsrfToken;
use Zan\Framework\Utilities\Encrpt\SimpleEncrypt;

class SimpleTokenParser implements TokenParserInterface
{

    public function generateToken($id, $tokenTime)
    {
        $raw = SimpleEncrypt::encrypt($id . ',' . $tokenTime);
        return new CsrfToken($id, $tokenTime, $raw);
    }

    public function parseToken($tokenRaw)
    {
        $raw = SimpleEncrypt::decrypt($tokenRaw);
        if (!$raw) {
            return null;
        }
        list($id, $tokenTime) = explode(',', $raw);
        return new CsrfToken($id, $tokenTime, $tokenRaw);
    }
}