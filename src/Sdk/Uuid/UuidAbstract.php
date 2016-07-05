<?php

namespace Zan\Framework\Sdk\Uuid;


abstract class UuidAbstract implements UuidInterface
{
    protected static $instance;

    protected function __construct()
    {
    }

    private function __clone()
    {
    }
}