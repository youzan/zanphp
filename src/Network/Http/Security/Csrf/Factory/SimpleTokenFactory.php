<?php


namespace Zan\Framework\Network\Http\Security\Csrf\Factory;


use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Security\Csrf\CsrfToken;
use Zan\Framework\Utilities\Encrpt\SimpleEncrypt;

class SimpleTokenFactory implements TokenFactoryInterface
{

    private $key;

    public function __construct()
    {
        $this->key = Config::get('csrf._default.key', NULL);
    }

    public function buildToken($id, $tokenTime)
    {
        $raw = SimpleEncrypt::encrypt($id . ',' . $tokenTime, $this->key);
        return new CsrfToken($id, $tokenTime, $raw);
    }

    public function buildFromRawText($tokenRaw)
    {
        $raw = SimpleEncrypt::decrypt($tokenRaw, $this->key);
        if (!$raw) {
            return null;
        }
        list($id, $tokenTime) = explode(',', $raw);
        return new CsrfToken($id, $tokenTime, $tokenRaw);
    }
}