<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 22:22
 */

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Foundation\Core\ObjectPool;
use Zan\Framework\Utilities\Types\ObjectArray;

class ConnectionPool extends ObjectPool{

    private $_freeConnection = null;

    private $_activeConnection = null;

    private $_config=null;



    public function __construct($config) {
        $this->_config = $config;
        init();
    }

    public function init() {
        //todo 读取配置文件
        $initConnection = $this->_config['init-connection'];

        $this->_freeConnection = new ObjectArray();
        $this->_activeConnection = new ObjectArray();
        for ($i=0; $i<$initConnection; $i++) {
            //todo 创建链接,存入数组
            $conn = new ConnBeanTest();
            $this->_freeConnection->push($conn);
        }

    }

    /**
     * @return
     * 获取链接
     */
    public function get() /* Connection */
    {
        if (count($this->_activeConnection) < $this->_config['maximum-connection-count']) {
            if (count($this->_freeConnection) > 0) {
                $conn = $this->_freeConnection->pop();
            }
        } else {
            return null;
        }
        if ($conn) {
            $this->_activeConnection->push($conn);
        }
        deferRelease($conn);
        return $conn;
    }

    public function release(Connection $conn)
    {
        $this->_freeConnection->push($conn);
        $this->_activeConnection->remove($conn);
        if (count($this->_freeConnection) == 1) {
            //唤醒等待事件
            $evtName = '' . '_free';
            Event::fire($evtName, [], false);
        }
    }

}