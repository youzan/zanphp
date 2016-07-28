<?php
namespace Zan\Framework\Utilities\File;
use Zan\Framework\Foundation\Contract\Async;

/**
 * Created by PhpStorm.
 * User: suqian
 * Date: 16/7/28
 * Time: 下午3:04
 */
class File implements Async
{
    protected $fileName = '';

    protected $size = 8000;

    protected $offset = 0;

    protected $callback = null;

    public function __construct(){
    }

    public function read($fileName,$size=8000,$offset=0){
        $this->fileName = $fileName;
        $this->size = $size;
        $this->offset = $offset;
        yield $this;
    }
    public function execute(callable $callback, $task){
        // TODO: Implement execute() method.
        $this->setCallback($this->getCallback())->readHandle();
    }

    public function readHandle(){
        swoole_async_read($this->fileName,$this->callback,$this->size,$this->offset);
    }

    public function setCallback(callable $callback){
        $this->callback = $callback;
        return $this;
    }

    private function getCallback(callable $callback)
    {
        return function($response) use ($callback) {
            call_user_func($callback, $response);
        };
    }
}