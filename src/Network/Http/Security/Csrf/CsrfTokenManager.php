<?php


namespace Zan\Framework\Network\Http\Security\Csrf;


use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Security\Csrf\Factory\SimpleTokenFactory;
use Zan\Framework\Network\Http\Security\Csrf\Factory\TokenFactoryInterface;
use Zan\Framework\Utilities\Encrpt\Uuid;

class CsrfTokenManager implements CsrfTokenManagerInterface
{
    private $csrfConfig;

    /**
     * @var TokenFactoryInterface
     */
    private $factory;

    /**
     * Creates a new CSRF provider using cookie
     *
     * @param TokenFactoryInterface|null $factory The token generator
     */
    public function __construct(TokenFactoryInterface $factory = null)
    {
        $this->factory = $factory ?: Di::make(SimpleTokenFactory::class);
        $this->csrfConfig = Config::get('csrf', []);
    }

    /**
     * {@inheritdoc}
     */
    public function createToken()
    {
        $id = Uuid::get();
        $time = time();
        return $this->factory->buildToken($id, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken(CsrfToken $token)
    {
        return $this->factory->buildToken($token->getId(), time());
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($tokenRaw)
    {
        $token = $this->factory->buildFromRawText($tokenRaw);
        printf("%s: %s\n", $token->getId(), date('Y-m-d H:i:s', $token->getTokenTime()));
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function isTokenValid(CsrfToken $token, array $modules)
    {
        if ($token and (time() - $token->getTokenTime()) < $this->getTimeToLive($modules)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeToLive(array $modules)
    {
        static $defaultTTL = 60;
        $cfg = $this->getStrategy($this->csrfConfig, $modules);
        $ttl = isset($cfg['ttl']) ? $cfg['ttl'] : $defaultTTL;
        var_dump($cfg);
        return $ttl;
    }

    private function getStrategy($config, $arr)
    {
        $name = array_shift($arr);
        if (isset($config[$name])) {
            if (empty($arr)) {
                return $config[$name];
            } elseif (is_array($result = $this->getStrategy($config[$name], $arr))) {
                return $result;
            }
        }

        return isset($config['_default']) ? $config['_default'] : $config;
    }
}