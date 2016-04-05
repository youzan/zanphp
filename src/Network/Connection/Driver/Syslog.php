<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 11:36
 */

namespace Zan\Framework\Network\Connection\Driver;


use Zan\Framework\Contract\Network\Connection;

class Syslog extends Base implements Connection
{
    public function closeSocket()
    {
        // TODO: Implement closeSocket() method.
    }
}