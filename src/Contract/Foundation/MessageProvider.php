<?php

namespace Zan\Framework\Contract\Foundation;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \Zan\Framework\Contract\Utilities\Types\MessageBag
     */
    public function getMessageBag();
}
