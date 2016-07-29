<?php
namespace Zan\Framework\Utilities\File;
use Zan\Framework\Foundation\Contract\Async;

/**
 * Created by PhpStorm.
 * User: suqian
 * Date: 16/7/28
 * Time: 下午3:13
 */
class OnceFile implements Async
{

    CONST READ = 0;
    CONST WRITE = 1;
    protected $handle = 0;
    protected $filename = '';
    protected $content = '';
    protected $callback = null;

    public  function getContent($fileName){
        $this->handle = self::READ;
        $this->filename = $fileName;
        yield $this;
    }

    public  function putContent($fileName,$content){
        $this->handle = self::WRITE;
        $this->filename = $fileName;
        $this->content = $content;
        yield $this;
    }

    public function execute(callable $callback, $task){
        if($this->handle == self::READ) {
            $this->setCallback($this->getReadCallback($callback))->readHandle();
        }else{
            $this->setCallback($this->getWriteCallback($callback))->writeHandle();
        }
    }

    public function readHandle(){
        swoole_async_readfile($this->filename,$this->callback);
    }

    public function writeHandle(){
        swoole_async_writefile($this->filename,$this->content,$this->callback);
    }

    public function setCallback(callable $callback){
        $this->callback = $callback;
        return $this;
    }

    private function getReadCallback(callable $callback)
    {
        return function($fileName,$content) use ($callback) {
            call_user_func($callback, $content);
        };
    }

    private function getWriteCallback(callable $callback)
    {
        return function($response,$contentLength) use ($callback) {
            $response = $response ? true : false;
            call_user_func($callback, $response);
        };
    }

}