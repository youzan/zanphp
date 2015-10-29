<?php
namespace Zan\Framework\Network\Contract;

interface Server {
    function onStart($server, $workerId); 
    function onConnect($server, $clientId, $fromId);
    function onReceive($server, $clientId, $fromId, $data);
    function onClose($server, $clientId, $fromId);
    function onShutdown($server, $workerId);
    function onTask($server, $taskId, $fromId, $data);
    function onFinish($server, $taskId, $data);
    function onTimer($server, $interval);
}