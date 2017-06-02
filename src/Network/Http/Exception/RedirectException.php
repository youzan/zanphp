<?php

namespace Zan\Framework\Network\Http\Exception;


use Zan\Framework\Foundation\Exception\BusinessException;

class RedirectException extends BusinessException
{
    public $redirectUrl;

    public function __construct($url, $message)
    {
        parent::__construct($message);
        $this->setRedirectUrl($url);
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param mixed $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }


}

