<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/5/20
 * Time: 16:32
 */

namespace Zan\Framework\Network\Server\WorkerStart;


use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Foundation\Core\Env;

class InitEnv implements Bootable{
    public function bootstrap($server)
    {
        Env::init();
    }
}