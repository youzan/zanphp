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

class Acl
{
    private $configKey = 'acl';

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->config = Config::get($this->configKey, null);
    }

    public function auth()
    {
        $cookie = (yield getCookieHandler());
        $sid = $cookie->get('sid', '');
        $userId = $cookie->get('user_id', 0);
        if ('' === $sid) {
            $cookie->set('redirect', $this->request->getFullUrl());
            yield RedirectResponse::create($this->config['login_url'], 302);
        } else {
            if (0 === $userId) {
                $userId = (yield Client::call('account.sso.getAdminIdBySid', ['sid' => $sid]));
                $cookie->set('user_id', $userId, 0);
            }
        }

        yield null;
    }

}
