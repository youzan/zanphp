<?php

namespace Zan\Framework\Sdk\Uuid;


interface UuidInterface
{
    public function get($tableName);
    public static function getInstance();
}