<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 22:42
 */

namespace Zan\Framework\Network\Common;


use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Network\Contract\Response;
use Zan\Framework\Network\Facade\ConnectionManager;

class FutureConnection implements Async {
    private $poolKey = null;
    private $callback = null;

    public function __construct($poolKey)
    {
        $this->poolKey = $poolKey;
        $this->init();
    }


    public function execute(callable $callback)
    {
        $this->callback = $callback;
    }

    private function init()
    {
        //have free conn
        $evtName = $this->poolKey . '_free';
        Event::once($evtName,[$this,'getConnection' ]);

        //bind timeout event
        $entTimeout = $this->poolKey . '_timeout';

    }

    public function getConnection()
    {
        $conn = ConnectionManager::get($this->poolKey);
        call_user_func($this->callback, $conn);
    }

    public function timeoutEvent() {

    }
}
