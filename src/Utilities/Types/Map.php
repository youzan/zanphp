<?php
namespace Zan\Framework\Utilities\Types;

class Map
{
    private $data = null;

    public function __construct()
    {
        $this->data = [];
    }

    public function get($key, $default=null)
    {
        if(!isset($this->data[$key])) {
            return $default;
        }

        return $this->data[$key];
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}
