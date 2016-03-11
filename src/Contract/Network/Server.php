<?php
namespace Zan\Framework\Contract\Network;

interface Server {

    public function start();
    public function stop();
    public function reload();
}