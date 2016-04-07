<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/4
 * Time: 00:57
 */

namespace Zan\Framework\Network\Connection\Engine;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

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

    public function execute(callable $callback)
    {
        $this->taskCallback = $callback;
    }

    private function init()
    {
        //have free conn
        $evtName = $this->connKey . '_free';
        Event::once($evtName,[$this,'getConnection' ]);

        //bind timeout event
        $entTimeout = $this->connKey . '_timeout';
        Event::once($evtName,[$this,'timeoutEvent' ]);

    }

    public function getConnection()
    {
        $conn = $this->connectionManager->get($this->connKey);
        call_user_func($this->taskCallback, $conn);
    }

    public function timeoutEvent()
    {
        //todo

    }
    
}