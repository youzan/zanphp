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
use Zan\Framework\Network\Common\Client;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\Types\Arr;

class Acl
{
    private $configKey = 'acl';
    protected $context = null;
    protected $request = null;

    public function __construct(Request $request, Context $context)
    {
        $this->request = $request;
        $this->context = $context;
        $this->config = Config::get($this->configKey, null);
    }

    public function auth()
    {
        $aclWhiteList = $this->config['white_list'];
        $currentPath = $this->request->getPath();
        if (in_array($currentPath, $aclWhiteList)) {
            return;
        }
        $cookie = (yield getCookieHandler());
        $sid = (yield $cookie->get('sid', ''));
        $userId = (yield $cookie->get('user_id', 0));
        if ('' === $sid) {
            $cookie->set('redirect', $this->request->getFullUrl(), 0);
            yield RedirectResponse::create($this->config['login_url'], 302);
            return;
        }
        if ($userId <= 0) {
            yield $this->setAdminInfoToCookie($sid);
        }else {
            $this->context->set('admin', (yield $this->request->cookie('admin',[])) );
        }
        yield null;
    }

    private function setAdminInfoToCookie($sid)
    {
        if (!$sid) {
            yield null;
            return;
        }
        $cookie = (yield getCookieHandler());
        $userId = (yield $this->getAdminIdBySid($sid));
        if ($userId <= 0) {
            yield null;
            return;
        }
        $adminInfo = (yield $this->getAdminInfoById($userId));
        if (empty($adminInfo)) {
            yield null;
            return;
        }
        $admin = ['user_id'=>$userId,'account'=>$adminInfo['account'],'avatar'=>$adminInfo['avatar'],'nickname'=>$adminInfo['nick_name']];
        $this->context->set('admin', $admin);
        yield $cookie->set('user_id',$userId,0);
        yield $cookie->set('admin', $admin, 0);

    }

    private function getAdminIdBySid($sid)
    {
        $resp = (yield Client::call('account.sso.getAdminIdBySid', ['sid' => $sid]));
        $result = $resp['code'] === 0 ? $resp['data'] : null;
        yield $result;
    }

    private function getAdminInfoById($id)
    {
        $resp = (yield Client::call('account.admin.getPersonalInfo', ['admin_id' => $id]));
        $result = $resp['code'] === 0 ? $resp['data'] : null;
        yield $result;
    }

}
