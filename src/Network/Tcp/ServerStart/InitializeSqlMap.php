<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/18
 * Time: 下午4:43
 */


namespace Zan\Framework\Network\Tcp\ServerStart;
use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Store\Database\Sql\SqlMapInitiator;

class InitializeSqlMap implements Bootable
{
    /**
     * @param
     */
    public function bootstrap($server)
    {

        SqlMapInitiator::getInstance()->init();
    }
}