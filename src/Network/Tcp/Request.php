<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 23:55
 */

namespace Zan\Framework\Network\Tcp;

use Zan\Framework\Contract\Network\Request as BaseRequest;
use Kdt\Iron\Nova\Nova;

class Request implements BaseRequest {
    private $route;
    private $serviceName;
    private $novaServiceName;
    private $methodName;
    private $args;
    private $fd;

    private $remoteIp;
    private $remotePort;
    private $fromId;
    private $seqNo;
    private $attachData;

    public function __construct($serviceName, $methodName, $args)
    {
        $this->serviceName = trim($serviceName);
        $this->methodName = trim($methodName);
        $this->args = $args;

        $this->formatRoute();
        $this->decodeArgs();
    }

    public function setFd($fd)
    {
        $this->fd = $fd;

        return $this;
    }

    public function getFd()
    {
        return $this->fd;
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

    public function getAttachData()
    {
        return $this->attachData;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }

    public function getNovaServiceName()
    {
        return $this->novaServiceName;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getRemote()
    {
        return [
            'ip' =>$this->remoteIp,
            'port' => $this->remotePort,
        ];
    }

    public function getRemotePort()
    {
        return $this->remotePort;
    }

    public function getFromId()
    {
        return $this->fromId;
    }

    public function getSeqNo()
    {
        return $this->seqNo;
    }

    private function formatRoute()
    {
        $serviceName = ucwords($this->serviceName, '.');
        $this->novaServiceName = str_replace('.','\\',$serviceName);

        $path = '/'. str_replace('.', '/', $serviceName) . '/';
        $this->route = $path . $this->methodName;
    }

    private function decodeArgs()
    {
        $this->args = Nova::decodeServiceArgs(
            $this->novaServiceName,
            $this->methodName,
            $this->args
        );
    }
}