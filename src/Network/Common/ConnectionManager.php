<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 22:23
 */

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\FutureConnection;
use Zan\Framework\Network\Common\ConnectionPool as Pool;
use Zan\Framework\Utilities\DesignPattern\Singleton;


class ConnectionManager {

    use Singleton;

    private  $_config=null;
    private  $poolMap = [];
    private  $_registry=[];

    public function __construct()
    {
        $this->configDemo();
    }

    public function init()
    {
        $connectionPool = new Pool($this->_config);
        $key = $this->_config['pool_name'];
        $this->_registry[] = $key;//注册连接池
        $this->poolMap[$key] = $connectionPool;
        return $this;
    }


    public function get($key) /* Connection */
    {
        if(!isset($this->poolMap[$key])){
            yield null;
        }
        $pool = $this->poolMap[$key];
        $conn = $pool->get();
        if ($conn) {
            yield $conn;
            return;
        }
        $conn = (yield new FutureConnection($this, $key));
//        deferRelease($conn);
    }

    public function release($key=null,Connection $conn)
    {
        $this->poolMap[$key]->release($conn);
    }

    public function configDemo() {
        $this->_config['host']= '192.168.66.202:3306';
        $this->_config['user'] = 'test_koudaitong';
        $this->_config['pool_name'] = 'p_zan';
        $this->_config['maximum-connection-count'] ='100';
        $this->_config['minimum-connection-count'] = '10';
        $this->_config['keeping-sleep-time'] = '10';//等待时间
        $this->_config['maximum-new-connections'] = '5';
        $this->_config['prototype-count'] = '5';
        $this->_config['init-connection'] = '10';
    }

}


