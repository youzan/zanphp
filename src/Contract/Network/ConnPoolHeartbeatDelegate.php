<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/30
 * Time: 上午10:42
 */

namespace Zan\Framework\Contract\Network;


interface ConnPoolHeartbeatDelegate
{
    /**
     * 返回相应连接心跳函数除去回调参数信息
     * mysql返回query函数参数
     * redis返回__call函数参数
     * tcp返回send函数参数
     * @return array
     */
    public function makeArguments();

    /**
     * 心跳回复检查
     * @return bool
     */
    public function onHeartbeat();
}