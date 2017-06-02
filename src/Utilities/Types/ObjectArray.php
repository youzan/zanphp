<?php

namespace Zan\Framework\Utilities\Types;


class ObjectArray
{

    private $map = [];

    public function push ($object)
    {
        $key = spl_object_hash($object);
        $this->map[$key] = $object;
    }

    public function pop()
    {
        return array_pop($this->map);
    }

    public function remove($object)
    {
        $key = spl_object_hash($object);
        if (!isset($this->map[$key])) {
            return false;
        }
        unset($this->map[$key]);
        return true;
    }

    public function length() {
        return count($this->map);
    }

    public function get($key)
    {
        return isset($this->map[$key]) ? $this->map[$key] : null;
    }

    public function isEmpty()
    {
        return empty($this->map);
    }
}