<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/4/5
 * Time: 上午11:19
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Network\Http\Response\RedirectResponse;
use Zan\Framework\Utilities\DesignPattern\Context;

class Acl
{
    private $configKey = 'acl';

    public function __construct(Request $request, Context $context)
    {
        $this->request = $request;
        $this->context = $context;
        $this->config = Config::get($this->configKey, null);
    }

    public function auth()
    {
        $cookie = (yield getCookieHandler());
        $sid = $cookie->get('sid');
        if (!$sid) {
            yield RedirectResponse::create($this->config['login_url'], 302);
        } else {
            $this->context->set('sid', $sid);
            yield null;
        }
    }

}
