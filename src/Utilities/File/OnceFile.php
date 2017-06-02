<?php

namespace Zan\Framework\Utilities\File;

use Zan\Framework\Foundation\Contract\Async;

class OnceFile implements Async
{
    const MAX_CHUNK = 1024 * 1024;
    const EOF = "";
    protected $offset = 0;

    CONST READ = 0;
    CONST WRITE = 1;
    protected $handle = 0;
    protected $filename = '';
    protected $content = '';
    /** @var callable */
    protected $callback = null;

    public function getContents($filename){
        $this->handle = self::READ;
        $this->filename = $filename;
        yield $this;
    }

    public function putContents($filename,$content){
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
        // swoole_async_readfile 废弃
        // swoole_async_readfile($this->filename, $this->callback);
        $this->content = "";
        $r = swoole_async_read($this->filename, function($filename, $content) {
            if ($content === self::EOF) {
                $cb = $this->callback;
                $cb($filename, $this->content);
                $this->content = "";
            } else {
                $this->content .= $content;
            }
        });

        if ($r === false) {
            $cb = $this->callback;
            $cb($this->filename, $this->content);
            $this->content = "";
        }
    }

    public function writeHandle(){
        // swoole_async_writefile 废弃
        // swoole_async_writefile($this->filename, $this->content, $this->callback);
        $content = substr($this->content, 0, self::MAX_CHUNK);
        if ($content === false) {
            $cb = $this->callback;
            $cb($this->filename, $this->offset);
            return;
        }

        $r = swoole_async_write($this->filename, $content, $this->offset, function($filename, $size) {
            $this->content = substr($this->content, $size);
            $this->offset += $size;

            if ($this->content !== false && strlen($this->content)) {
                $this->writeHandle();
            } else {
                $cb = $this->callback;
                $cb($filename, $this->offset);
                $this->content = "";
                $this->offset = 0;
            }
        });

        if ($r === false) {
            $cb = $this->callback;
            $cb($this->filename, $this->offset);
            $this->content = "";
            $this->offset = 0;
        }
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
            call_user_func($callback, $contentLength);
        };
    }

}