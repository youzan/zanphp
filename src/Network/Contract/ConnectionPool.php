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

    public function configDemo() {
        $config = new Config();
        $config->set('host', '192.168.66.202:3306');
        $config->set('user', 'test_koudaitong');
        $config->set('pool_name', 'p_zan');
        $config->set('maximum-connection-count', '100');
        $config->set('minimum-connection-count', '10');
        $config->set('keeping-sleep-time', '90000');
        $config->set('maximum-new-connections', '5');
        $config->set('prototype-count', '5');

    }
}