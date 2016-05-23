<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 16/3/21
 * Time: 18:10
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\Path;
use Exception;

class FileWriter implements Async, LogWriter{

    private $path;
    private $postData;
    private $module;
    private $type;
    private $callback;

    public function __construct($app, $module, $type, $path){
        $this->path = Path::getLogPath();
        if(!$path){
            throw new Exception('path not be null');
        }
        $this->path = $this->path.$path;
        $this->module = $module;
        $this->type   = $type;
        return $this;
    }

    public function execute(callable $callback){
        $this->callback = $callback;
    }

    public function write_callback($file, $write){
        call_user_func($this->callback, $write);
    }

    public function write($log, $level){
        $this->getLogData($log, $level);
        swoole_async_write($this->path, $this->postData, -1, [$this, 'write_callback']);
        yield $this;
    }

    private function getLogData($log, $level){
        if(is_array($log)){
            $log = json_encode($log,JSON_UNESCAPED_UNICODE);
        }
        $this->postData .= sprintf("[%s]\t[%s]\t[%s]\t%s\t%s\n", date('Y-m-d H:i:s'), $level,  $this->module, $this->type, $log);
    }
}