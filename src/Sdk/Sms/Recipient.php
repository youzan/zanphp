<?php
namespace Zan\Framework\Sdk\Sms;

class Recipient {
    private $sender;

    private $receiver;

    private $channel;

    function __construct($channel, $receiver, $sender = '')
    {
        $this->channel = $channel;
        $this->receiver = $receiver;
        $this->sender = $sender;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return mixed
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    public function addSingleReceiver($receiver) {
        $this->receiver = array_merge($this->receiver, array($receiver));
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param string $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }


}