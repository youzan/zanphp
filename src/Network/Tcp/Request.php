<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 23:55
 */

namespace Zan\Framework\Network\Tcp;

use Zan\Framework\Contract\Network\Request as BaseRequest;

class Request implements BaseRequest {
    private $route;
    private $serviceName;
    private $methodName;
    private $args;

    private $remoteIp;
    private $remotePort;
    private $fromId;
    private $seqNo;
    private $attachData;

    public function __construct($serviceName, $methodName, $args)
    {
        $this->serviceName = $serviceName;
        $this->methodName = $methodName;
        $this->args = $args;
    }

    public function setRemote($ip, $port)
    {
        $this->remoteIp = $ip;
        $this->remotePort = $port;

        return $this;
    }

    public function setFromId($fromId)
    {
        $this->fromId = $fromId;

        return $this;
    }

    public function setSeqNo($seqNo)
    {
        $this->seqNo = $seqNo;

        return $this;
    }

    public function setAttachData($attachData)
    {
        $this->attachData = $attachData;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @return mixed
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @return mixed
     */
    public function getRemote()
    {
        return [
            'ip' =>$this->remoteIp,
            'port' => $this->remotePort,
        ];
    }

    /**
     * @return mixed
     */
    public function getRemotePort()
    {
        return $this->remotePort;
    }

    /**
     * @return mixed
     */
    public function getFromId()
    {
        return $this->fromId;
    }

    /**
     * @return mixed
     */
    public function getSeqNo()
    {
        return $this->seqNo;
    }

}