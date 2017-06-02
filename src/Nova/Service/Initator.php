<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 14:56
 */

namespace Zan\Framework\Nova\Service;


use Zan\Framework\Nova\Foundation\Traits\InstanceManager;

class Initator
{
    use InstanceManager;

    public function init(array $configs)
    {
        NovaConfig::getInstance()->setConfig($configs);
        Scanner::getInstance()->scan();
    }
}