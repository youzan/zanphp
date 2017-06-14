<?php


namespace Zan\Framework\Network\Http\ServerStart;
use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Store\Database\Sql\SqlMapInitiator;

class InitializeSqlMap implements Bootable
{

    public function bootstrap($server)
    {
        SqlMapInitiator::getInstance()->init();
    }
}