<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/4/21
 * Time: 上午11:46
 */

namespace Zan\Framework\Network\Connection\Driver;

use Zan\Framework\Contract\Network\Connection;
use swoole_client as SwooleClient;


class KVStore extends Base implements Connection
{
    protected function closeSocket()
    {
        try {
            $this->getSocket()->close();
        } catch (\Exception $e) {
            //todo log
        }
    }
}