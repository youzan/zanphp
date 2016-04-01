<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/3/31
 * Time: 下午5:28
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Http\Request\Request;
use swoole_http_response as SwooleHttpResponse;

class Cookie
{
    private $configKey = 'cookie';
    private $config;
    private $request;
    private $response;

    public function __construct(Request $request, SwooleHttpResponse $swooleResponse)
    {
        $this->init($request, $swooleResponse);
    }

    private function init(Request $request, SwooleHttpResponse $swooleResponse)
    {
        $config = Config::get($this->configKey, null);
        if (!$config) {
            throw new InvalidArgumentException('cookie config is required');
        }
        $this->config = $config;
        $this->request = $request;
        $this->response = $swooleResponse;
    }

    public function get($key, $default = null)
    {
        $cookies = $this->request->cookies;
        if (!$key) {
            yield $default;
        }

        yield $cookies->get($key, $default);
    }

    public function set($key, $value = null, $expire, $path, $domain, $secure, $httponly)
    {
        if (!$key) {
            return false;
        }
        $expire = isset($expire) ? $expire : $this->config['expire'];
        $path = isset($path) ? $path : $this->config['path'];
        $domain = isset($domain) ? $domain : $this->config['domain'];
        $secure = isset($secure) ? $secure : $this->config['secure'];
        $httponly = isset($httponly) ? $httponly : $this->config['httponly'];

        $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

}
