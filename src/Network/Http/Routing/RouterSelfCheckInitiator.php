<?php

namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Http\Routing\UrlRule;
use Zan\Framework\Network\Http\Routing\RouterSelfCheck;
use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Dir;
use Zan\Framework\Foundation\Core\Config;

class RouterSelfCheckInitiator
{
    use Singleton;

    public function init()
    {
        $routerSelfCheck = RouterSelfCheck::getInstance();
        $routerSelfCheck->setUrlRules(UrlRule::getRules());
        $routerSelfCheck->setCheckList($this->_getCheckList());
        $routerSelfCheck->check();
    }

    private function _getCheckList()
    {
        $checkList = [];
        $checkListFiles = Dir::glob(Config::get('path.routing'), '*.check.php');
        if (!is_array($checkListFiles) or empty($checkListFiles) ) {
            return [];
        }
        foreach ($checkListFiles as $file)
        {
            $list = include $file;
            if (!is_array($list)) continue;
            $checkList = Arr::merge($checkList, $list);
        }
        return $checkList;
    }
} 