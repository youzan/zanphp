<?php

namespace Zan\Framework\Nova\Service;

use Zan\Framework\Nova\Foundation\Traits\InstanceManager;

class ClassMap
{
    use InstanceManager;

    private $sepcMap = [];
    private $search  = '\\Servicespecification\\';
    private $replace = '\\Service\\';
    public function setSpec($key, $object)
    {
        $key = $this->formatKey($key);
        $this->sepcMap[$key] = $object;
    }

    public function getSpec($key, $default=null)
    {
        if(!isset($this->sepcMap[$key])){
            return $default;
        }
        return $this->sepcMap[$key];
    }

    public function getAllSpec()
    {
        return $this->sepcMap;
    }

    private function formatKey($key)
    {
        return str_replace($this->search, $this->replace, $key);
    }

}