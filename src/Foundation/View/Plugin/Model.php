<?php

namespace Zan\Framework\Foundation\View\Plugin;


class Model {
    private $key = null;

    public function __construct($key, array $config)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getKeyHash()
    {

    }

    public function getRules()
    {

    }
}