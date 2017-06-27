<?php

namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\View\Plugin\KeyHash;

class Form
{
    private $keyHash = null;

    public function __construct()
    {
        $this->keyHash = new KeyHash();
    }

    public function model($modelKey)
    {

    }

    public function save()
    {

    }
}