<?php

namespace Zan\Framework\Contract\Utilities\Validation;

use RuntimeException;
use Zan\Framework\Contract\Foundation\MessageProvider;

class ValidationException extends RuntimeException
{
    /**
     * The message provider implementation.
     * @var \Zan\Framework\Contract\Foundation\MessageProvider
     */
    protected $provider;

    /**
     * Create a new validation exception instance.
     * @param  \Zan\Framework\Contract\Foundation\MessageProvider $provider
     */
    public function __construct(MessageProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get the validation error message provider.
     * @return \Zan\Framework\Contract\Utilities\Types\MessageBag
     */
    public function errors()
    {
        return $this->provider->getMessageBag();
    }

    /**
     * Get the validation error message provider.
     * @return \Zan\Framework\Contract\Foundation\MessageProvider
     */
    public function getMessageProvider()
    {
        return $this->provider;
    }
}
