<?php


namespace Zan\Framework\Network\Http\Security\Csrf;


use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\ZanException;
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
        if ($token) {
            printf("%s: %s\n", $token->getId(), date('Y-m-d H:i:s', $token->getTokenTime()));
        }
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function isTokenValid(array $modules, CsrfToken $token = null)
    {
        if (empty($modules)) {
            throw new ZanException('Not support request');
        }
        if ($token and (time() - $token->getTokenTime()) < $this->getTTL($modules)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTTL(array $modules)
    {
        static $defaultTTL = 1800;
        $cfg = $this->getStrategy($this->csrfConfig, $modules);
        $ttl = isset($cfg['ttl']) ? $cfg['ttl'] : $defaultTTL;
        return $ttl;
    }

    private function getStrategy($config, $arr)
    {
        if (is_array($arr) and count($arr) > 0) {
            $name = array_shift($arr);
            if (isset($config[$name])) {
                if (empty($arr)) {
                    return $config[$name];
                } else {
                    $result = $this->getStrategy($config[$name], $arr);
                    if (is_array($result)) {
                        return $result;
                    }
                }
            }
        }
        return isset($config['_default']) ? $config['_default'] : $config;
    }
}