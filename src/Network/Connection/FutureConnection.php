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
    
    public function __construct($connKey, $timeout=0)
    {
        if(!is_int($timeout)){
            throw new InvalidArgumentException('invalid timeout for Future[Connection]');
        }
        
        $this->connKey = $connKey;
        $this->timeout = $timeout;
    }

    public function execute(callable $callback)
    {
        $this->taskCallback = $callback;
    }
    
}