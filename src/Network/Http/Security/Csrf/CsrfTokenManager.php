<?php


namespace Zan\Framework\Network\Http\Security\Csrf;


use Zan\Framework\Network\Http\Security\Csrf\TokenParser\SimpleTokenParser;
use Zan\Framework\Network\Http\Security\Csrf\TokenParser\TokenParserInterface;
use Zan\Framework\Utilities\Encrpt\Uuid;

class CsrfTokenManager implements CsrfTokenManagerInterface
{

    /**
     * @var TokenParserInterface
     */
    private $parser;

    /**
     * Creates a new CSRF provider using PHP's native session storage.
     *
     * @param TokenParserInterface|null $generator The token generator
     */
    public function __construct(TokenParserInterface $generator = null)
    {
        $this->parser = $generator ?: new SimpleTokenParser();
    }

    /**
     * @param $id
     * @param $tokenTime
     * @return CsrfToken
     */
    public function createToken()
    {
        $id = Uuid::get();
        $time = time();
        return $this->parser->generateToken($id, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken(CsrfToken $token)
    {
        return $this->parser->generateToken($token->getId(), time());
    }

    public function parseToken($tokenRaw)
    {
        $token = $this->parser->parseToken($tokenRaw);
        printf("%s: %s\n", $token->getId(), date('Y-m-d H:i:s', $token->getTokenTime()));
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function isTokenValid(CsrfToken $token)
    {
        if ($token and (time() - $token->getTokenTime()) < self::EXPIRE_TIME) {
            return true;
        } else {
            return false;
        }
    }
}