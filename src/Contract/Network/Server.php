<?php
namespace Zan\Framework\Contract\Network;

interface Server {
    public function onStart($swooleServer);
    public function onShutdown($swooleServer);
    public function onWorkerStart($swooleServer, $workerId);
    public function onWorkerStop($swooleServer, $workerId);
    public function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode, $sigNo);
}