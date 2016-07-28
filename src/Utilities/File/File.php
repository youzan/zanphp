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

    protected $isEof = false;

    protected $fileSize = 0;

    public function __construct($fileName,$size=8000,$offset=0){
        $this->fileName = $fileName;
        $this->size = $size;
        $this->offset = $offset;
    }

    public function read(){
        yield $this;
    }

    public function eof(){
        yield $this->isEof;
    }

    public function execute(callable $callback, $task){
        // TODO: Implement execute() method.
        $this->setCallback($this->getCallback($callback))->readHandle();
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
        return function($fileName,$content) use ($callback) {
            $len = strlen($content);
            $this->offset += $len;
            if($len < $this->size){
                $this->isEof = true;
            }
            call_user_func($callback, $content);
        };
    }
}