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


class Cookie
{
    private $configKey = 'cookie';

    public function __construct(Request $request, $swooleResponse)
    {
        $this->init();
    }

    private function init()
    {
        $config = Config::get($this->configKey, null);
        if (!$config) {
            throw new InvalidArgumentException('cookie config is required');
        }
    }

    public function set($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {

    }

    public static function get()
    {

    }
}
