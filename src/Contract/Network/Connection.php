<?php

namespace Zan\Framework\Contract\Network;


interface Connection
{
    public function getSocket();

    public function release();

    public function close();

    public function getEngine();

    public function getConfig();

    public function heartbeat();
}