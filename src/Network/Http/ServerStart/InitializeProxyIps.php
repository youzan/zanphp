<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/5/8
 * Time: 下午2:46
 */
namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Request\BaseRequest;

class InitializeProxyIps
{
    /**
     * @param \Zan\Framework\Network\Http\Server $server
     */
    public function bootstrap($server)
    {
        $proxy = Config::get("proxy");
        if (is_array($proxy)) {
            BaseRequest::setTrustedProxies($proxy);
        }
    }
}
