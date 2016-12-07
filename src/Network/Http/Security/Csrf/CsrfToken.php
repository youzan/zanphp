<?php


namespace Zan\Framework\Network\Http\Security\Csrf;


class CsrfToken
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $tokenTime;

    /**
     * @var string
     */
    private $raw;

    public function __construct($id, $tokenTime, $raw)
    {
        $this->id = $id;
        $this->tokenTime = $tokenTime;
        $this->raw = $raw;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTokenTime()
    {
        return $this->tokenTime;
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function __toString()
    {
        return $this->tokenTime;
    }
}