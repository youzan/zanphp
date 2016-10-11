<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/4
 * Time: 00:57
 */

namespace Zan\Framework\Network\Connection;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Foundation\Core\Event;

class FutureConnection implements Async
{
    private $connKey = '';
    private $timeout = 0;
    private $taskCallback = null;
    private $connectionManager = null;
    
    public function __construct($connectionManager, $connKey, $timeout=0)
    {
        if(!is_int($timeout)){
            throw new InvalidArgumentException('invalid timeout for Future[Connection]');
        }
        $this->connectionManager = $connectionManager;
        $this->connKey = $connKey;
        $this->timeout = $timeout;
        $this->init();
    }

    public function execute(callable $callback, $task)
    {
        $this->taskCallback = $callback;
    }

    private function init()
    {
        $evtName = $this->connKey . '_free';
        Event::once($evtName,[$this,'getConnection' ]);

        $evtName = $this->connKey . '_connect_timeout';
        Event::once($evtName,[$this,'onConnectTimeout' ]);
    }

    public function getConnection()
    {
        Task::execute($this->doGeting());
    }

    public function doGeting()
    {
        $conn = (yield $this->connectionManager->get($this->connKey));
        call_user_func($this->taskCallback, $conn);
    }

    public function onConnectTimeout() {
        call_user_func($this->taskCallback, null, new \RuntimeException("futureConnection connected timeout"));
        unset($this->taskCallback);
    }
}