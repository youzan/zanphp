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
        $this->request = $request;
        $this->response = $swooleResponse;
    }

    public function get($name, $default = '')
    {
        if (!$name) {
            yield '';
        }

        if (!isset($this->request->cookies[$name])) {
            yield $default;
        }
        yield $this->request->cookies[$name];
    }

    public function set($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {

    }

}
