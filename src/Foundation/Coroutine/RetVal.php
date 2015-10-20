<?php
namespace Zan\Framework\Foundation\Coroutine;

class RetVal
{

    protected $info;

    public function __construct($info)
    {

        $this->info = $info;
    }
}