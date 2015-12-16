<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 22:22
 */

namespace Zan\Framework\Network\Contract;

use Zan\Framework\Foundation\Core\ObjectPool;

class ConnectionPool extends ObjectPool{

    public function get() /* Connection */
    {

    }

    public function release(Connection $conn)
    {

    }
}