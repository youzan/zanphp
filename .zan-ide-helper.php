<?php
class UnitTest extends \Zan\Framework\Testing\UnitTest {}
class TcpServer extends \Zan\Framework\Network\Tcp\Server {}

if (!function_exists("nova_encode")) {
    /**
     * @param string $service_name
     * @param string $method_name
     * @param int $ip
     * @param int $port
     * @param int $seq_no
     * @param string $attach_data
     * @param string $in_data
     * @param string $out_buffer
     * @return bool
     */
    function nova_encode($service_name, $method_name, $ip, $port, $seq_no, $attach_data, $in_data, &$out_buffer) {
        return false;
    }
}
if (function_exists("nova_decode")) {
    /**
     * @param string $in_buffer
     * @param string $service_name
     * @param string $method_name
     * @param int $ip
     * @param int $port
     * @param int $seq_no
     * @param string $attach_data
     * @param string $out_data
     * @return bool
     */
    function nova_decode($in_buffer, &$service_name, &$method_name, &$ip, &$port, &$seq_no, &$attach_data, &$out_data) {
        return false;
    }
}


class swoole_connpool
{
    /**
     * 连接正常
     */
    const SWOOLE_CONNNECT_OK = 1;

    /**
     * 连接异常
     */
    const SWOOLE_CONNNECT_ERR = 2;

    /**
     * TCP 连接池
     */
    const SWOOLE_CONNPOOL_TCP = 1;

    /**
     * redis 连接池
     */
    const SWOOLE_CONNPOOL_REDIS = 2;

    /**
     * mysql 连接池
     */
    const SWOOLE_CONNPOOL_MYSQL = 3;

    /**
     * http 连接池
     */
    const SWOOLE_CONNPOOL_HTTP = 4;

    /**
     * \swoole_connpool constructor.
     * @param int $connPoolType
     */
    public function __construct(int $connPoolType) { }

    /**
     * createConnPool
     * 创建连接池，接口内部对所有已经设置的参数进行校验
     *
     * @param int $minPoolnum 连接池最小对象个数
     * @param int $maxPoolnum 连接池最大对象个数
     * @return bool
     */
    public function createConnPool(int $minPoolnum, int $maxPoolnum){ }

    /**
     * setCfg 连接配置，支持重置
     * @param array $cfg
     * @param array $cfg 连接池连接配置信息, 参见mysql／redis 连接信息，createConnpool 会对该参数进行校验
     * @return bool
     *
     * hbTimeout
     * connectTimeout
     * hbIntervalTime 默认500
     *
     * 心跳需要同时配置：
     * hbIntervalTime
     * on hbConstruct
     * on hbCheck
     */
    public function setConfig(array $cfg){ }

    /**
     *  on 回调设置
     *
     * @param string $hbCbName 回调名称
     * ```
     * [
     * "hbConstruct" #string     心跳消息构造
     * "hbCheck"  #string     心跳回复校验
     * ]
     * ```
     * @param callable $hbCallback 回调函数，参见hbConstruct／hbCheck，
     * @return bool
     */
    public function on(string $hbCbName, callable $hbCallback){ }

    /**
     * get 获取连接对象
     *
     * @param callable $objCall 对象回调，参见@objectCallback
     * @param int $timeout 超时时间
     * @return int 请求ID，false：接口调用失败
     */
    public function get(int $timeout, callable $objCall){ }

    /**
     * release
     * 释放连接对象，会对用户参数进行校验
     *
     * @param \swoole_client|\swoole_redis|\swoole_mysql|\swoole_http_client $connObj
     * @param int $conStatus [option] 连接状态，可选，默认为SWOOLE_CONNOBJ_CONNECTED
     * @return bool
     */
    public function release($connObj, int $conStatus = self::SWOOLE_CONNNECT_OK){}

    /**
     * destroy 释放连接池对象，调用此接口前，需要将连接对象全部释放
     */
    public function destroy() { }

    /**
     * @return  array
     * [
     *  "all_conn_obj" => 0,
     *  "idle_conn_obj" => 0,
     * ]
     */
    public function getStatInfo() { }
}

/**
 * hbConstruct
 * 心跳信息构造
 *
 * @return array 发送心跳参数
 */
function hbConstruct() {
    return [
//        "method" => "query",
//        "args" => [$sql],
    ];
}

/**
 * hbCheck
 * 心跳回复内容检测
 *
 * @param \swoole_connpool $pool 连接池对象
 * @param |swoole_client|swoole_http_client|swoole_mysql|swoole_redis $conn 连接对象
 * @param mixed $data 心跳回复内容
 * @return true ：连接正常，false：连接异常
 */
function hbCheck(\swoole_connpool $pool, $conn, $data) { }

/**
 * objectCallback
 * 异步获取对象回调接口
 *
 * @param \swoole_connpool $pool 连接池对象
 * @param |swoole_client|swoole_http_client|swoole_mysql|swoole_redis|null $conn 连接对象
 *  获取对象失败时，返回空
 */
function objectCallback(\swoole_connpool $pool, $conn) {
    // 说明： 使用$connObj前需判断$connObj是否为空
    if (empty($conn))  {
        return;
    }
    // to do something
}

