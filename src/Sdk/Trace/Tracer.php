<?php
namespace Zan\Framework\Sdk\Trace;

abstract class Tracer
{
    protected $root_id;
    protected $parent_id;
    protected $remoteCallMsgId;

    //初始化Trace消息头部
    abstract public function initHeader($msgId = null);

    //Transaction适合记录跨越系统边界的程序访问行为,比如远程调用，数据库调用，也适合执行时间较长的业务逻辑监控，Transaction用来记录一段代码的执行时间和次数。
    abstract public function transactionBegin($type, $name);

    abstract public function transactionEnd($handle, $status, $sendData = '');

    //Event	用来记录一件事发生的次数，比如记录系统异常，它和transaction相比缺少了时间的统计，开销比transaction要小。
    abstract public function logEvent($type, $status, $name = "", $context = "");

    //上报Trace数据,可实现为协程或函数,非阻塞
    abstract public function uploadTraceData();

    public function getRootId()
    {
        return $this->root_id;
    }

    public function getParentId()
    {
        return $this->parent_id;
    }

    public function setRemoteCallMsgId($remoteCallMsgId)
    {
        $this->remoteCallMsgId = $remoteCallMsgId;
    }

    public function getRemoteCallMsgId()
    {
        return $this->remoteCallMsgId;
    }
}