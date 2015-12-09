<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\DesignPattern\Singleton;

abstract class ObjectPool {
    protected $initialNum = 1;
    protected $maxNum = 10;
    protected $maxDynamicNum = 0;

    protected $pool = [];
    protected $usedObjectMap = [];

    use Singleton;
    private function __construct() {
        $this->pool = [];
        $this->usedObjectMap = [];
    }

    abstract protected function createObject();
    abstract public function get($timeout=0);
    abstract public function release($object);
    abstract public function refresh($object);

    public function init($maxNum, $initalNum=0, $maxDynamicNum=0) {
        $this->setMaxNum($maxNum);
        $this->setInitalNum($initalNum);
        $this->setMaxDynamicNum($maxDynamicNum);
        $this->initPool();

        return $this;
    }

    protected function setInitalNum($num) {
        if ( !is_int($num) ) {
            throw new InvalidArgument('invalid initialNum (not int) for ObjectPool');
        }

        if ($num < 0) {
            throw new InvalidArgument('invalid initialNum (less than 0) for ObjectPool');
        }

        $this->initialNum = $num;
    }

    protected function setMaxNum($num) {
        if ( !is_int($num) ) {
            throw new InvalidArgument('invalid maxNum (not int) for ObjectPool');
        }

        if ($num < 1) {
            throw new InvalidArgument('invalid maxNum (less than 1) for ObjectPool');
        }

        $this->maxNum = $num;
    }

    protected function setMaxDynamicNum($num) {
        if ( !is_int($num) ) {
            throw new InvalidArgument('invalid maxDynamicNum (not int) for ObjectPool');
        }

        if ($num < 0) {
            throw new InvalidArgument('invalid maxDynamicNum (less than 1) for ObjectPool');
        }

        $this->maxDynamicNum = $num;
    }

    protected function initPool() {
        if ($this->initialNum < 1) {
            return false;
        }

        for($i=0; $i<$this->initialNum; $i++) {
            $this->pool[] = $this->createObject();
        }
    }
}
