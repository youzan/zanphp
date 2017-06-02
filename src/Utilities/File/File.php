<?php

namespace Zan\Framework\Utilities\File;
use Zan\Framework\Foundation\Contract\Async;

class File implements Async
{
    const MAX_CHUNK = 1024 * 1024;
    const EOF = "";

    CONST READ = 0;
    CONST WRITE = 1;

    protected $filename = '';

    protected $size = 8000;

    protected $offset = 0;

    /** @var callable */
    protected $callback = null;

    protected $isEof = false;

    protected $handle = 0;

    protected $content = '';

    public function __construct($fileName){
        $this->filename = $fileName;
    }

    /**
     * @param int $length -1 读取全部文件
     * @return \Generator
     */
    public function read($length = 8000){
        $this->handle = self::READ;
        $this->size = $length;
        yield $this;
    }

    public function eof(){
        return $this->isEof;
    }

    public function seek($offset = -1){
        return $this->offset = $offset;
    }

    public function tell(){
        return $this->offset;
    }

    public function write($content){
        $this->handle = self::WRITE;
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
        $this->content = "";

        $r = swoole_async_read($this->filename, function($filename, $content) {
            if ($content === self::EOF) {
                $cb = $this->callback;
                $cb($filename, $this->content);
                $this->content = "";
            } else {
                $this->content .= $content;
            }
        }, $this->size, $this->offset);

        if ($r === false) {
            $cb = $this->callback;
            $cb($this->filename, $this->content);
            $this->content = "";
        }
    }

    public function writeHandle($hasRead = 0){
        $content = substr($this->content, 0, self::MAX_CHUNK);
        if ($content === false) {
            $cb = $this->callback;
            $cb($this->filename, $hasRead);
            return;
        }

        $r = swoole_async_write($this->filename, $content, $this->offset, function($filename, $size) use($hasRead) {
            $this->content = substr($this->content, $size);
            $this->offset += $size;

            if ($this->content !== false && strlen($this->content)) {
                $this->writeHandle($hasRead + $size);
            } else {
                $cb = $this->callback;
                $cb($filename, $hasRead + $size);
            }
        });

        if ($r === false) {
            $cb = $this->callback;
            $cb($this->filename, $hasRead);
            return;
        }
    }

    public function setCallback(callable $callback){
        $this->callback = $callback;
        return $this;
    }

    private function getReadCallback(callable $callback)
    {
        return function($filename,$content) use ($callback) {
            $len = strlen($content);
            $this->offset += $len;
            if($len < $this->size){
                $this->isEof = true;
            }
            call_user_func($callback, $content);
        };
    }

    private function getWriteCallback(callable $callback)
    {
        return function($response,$contentLength) use ($callback) {
            call_user_func($callback, $contentLength);
        };
    }
}