<?php

class ObjectPool {
    protected $factory      = null;
    protected $initialNum   = 1;
    protected $maxNum       = 10;
    protected $dynamicNum   = 5;
    protected $pool         = [];


    public function __construct($factory=null) {
        $this->pool = [];    

        if (null !== $factory) {
           $this->setFactory($factory); 
        }
    }

    public function setFactory($factory) {
        $this->factory = $factory;

        return $this;
    }    

    public function setInitalNum($num) {
        $this->initialNum = $num;

        return $this;
    }

    public function setMaxNum($num) {
        $this->maxNum = $num;

        return $this;
    }

    public function setDynamicNum($num) {
        $this->dynamicNum = $num;

        return $this;
    }

    public function init() {
             
    }

}
