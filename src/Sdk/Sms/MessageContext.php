<?php
namespace Zan\Framework\Sdk\Sms;

class MessageContext {

    private $templateName;

    private $params;

    function __construct($templateName, $params)
    {
        $this->params = $params;
        $this->templateName = $templateName;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @param mixed $templateName
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
    }


}