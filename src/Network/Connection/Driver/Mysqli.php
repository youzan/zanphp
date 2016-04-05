<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/4
 * Time: 01:29
 */

namespace Zan\Framework\Network\Connection\Driver;


use Zan\Framework\Contract\Network\Connection;

class Mysqli extends Base implements Connection
{
    public function closeSocket()
    {
        return $this->socket->close(); 
    }
    
    public function ping()
    {
        
    }
}