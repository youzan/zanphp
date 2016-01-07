<?php
namespace Zan\Framework\Network\Contract;

interface Server {

    public function start();
    public function stop();
    public function reload();
}