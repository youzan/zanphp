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

    public  function getContents($filename){
        $this->handle = self::READ;
        $this->filename = $filename;
        yield $this;
    }

    public  function putContents($filename,$content){
        $this->handle = self::WRITE;
        $this->filename = $filename;
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
        return function($filename,$content) use ($callback) {
            call_user_func($callback, $content);
        };
    }

    private function getWriteCallback(callable $callback)
    {
        return function($filename,$contentLength) use ($callback) {
            $response = $contentLength > 0 ? true : false;
            call_user_func($callback, $response);
        };
    }

}