<?php

namespace Kdt\Iron\Nova\Network;


class ClientContext
{
    private $_reqServiceName;
    private $_reqMethodName;
    private $_reqSeqNo;  
    private $_attachmentContent;
    private $_outputStruct;
    private $_exceptionStruct;
    private $_packer;
    private $_cb;
    private $_task;
    private $_startTime;//us

    /**
     * @return mixed
     */
    public function getTask()
    {
        return $this->_task;
    }

    /**
     * @param mixed $task
     */
    public function setTask($task)
    {
        $this->_task = $task;
    }

    /**
     * @return mixed
     */
    public function getCb()
    {
        return $this->_cb;
    }

    /**
     * @param mixed $cb
     */
    public function setCb($cb)
    {
        $this->_cb = $cb;
    }

    /**
     * @return mixed
     */
    public function getPacker()
    {
        return $this->_packer;
    }

    /**
     * @param mixed $packer
     */
    public function setPacker($packer)
    {
        $this->_packer = $packer;
    }

    /**
     * @return mixed
     */
    public function getReqServiceName()
    {
        return $this->_reqServiceName;
    }

    /**
     * @param mixed $reqServiceName
     */
    public function setReqServiceName($reqServiceName)
    {
        $this->_reqServiceName = $reqServiceName;
    }

    /**
     * @return mixed
     */
    public function getReqMethodName()
    {
        return $this->_reqMethodName;
    }

    /**
     * @param mixed $reqMethodName
     */
    public function setReqMethodName($reqMethodName)
    {
        $this->_reqMethodName = $reqMethodName;
    }

    /**
     * @return mixed
     */
    public function getReqSeqNo()
    {
        return $this->_reqSeqNo;
    }

    /**
     * @param mixed $reqSeqNo
     */
    public function setReqSeqNo($reqSeqNo)
    {
        $this->_reqSeqNo = $reqSeqNo;
    }

    /**
     * @return mixed
     */
    public function getAttachmentContent()
    {
        return $this->_attachmentContent;
    }

    /**
     * @param mixed $attachmentContent
     */
    public function setAttachmentContent($attachmentContent)
    {
        $this->_attachmentContent = $attachmentContent;
    }

    /**
     * @return mixed
     */
    public function getOutputStruct()
    {
        return $this->_outputStruct;
    }

    /**
     * @param mixed $outputStruct
     */
    public function setOutputStruct($outputStruct)
    {
        $this->_outputStruct = $outputStruct;
    }

    /**
     * @return mixed
     */
    public function getExceptionStruct()
    {
        return $this->_exceptionStruct;
    }

    /**
     * @param mixed $exceptionStruct
     */
    public function setExceptionStruct($exceptionStruct)
    {
        $this->_exceptionStruct = $exceptionStruct;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->_startTime;
    }

    /**
     */
    public function setStartTime()
    {
        $this->_startTime = microtime(true);
    }


}