<?php
namespace Zan\Framework\Network\Contract;

interface Server {
    function start();
    function stop();
    function reload();
}