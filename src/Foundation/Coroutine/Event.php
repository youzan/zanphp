<?php
namespace Zan\Framework\Foundation\Coroutine;

class Event {
    private $evtMap  = [];
    private $evtChain = null;

    public function __construct() {
        $this->evtMap = [];
        $this->evtChain = new EventChain($this);
    }

    public function register($evtName) {
        if (!isset($this->evtMap[$evtName])) {
            $this->evtMap[$evtName] = []; 
        } 
    }

    public function unregister($evtName) {
        if (isset($this->evtMap[$evtName])) {
            unset($this->evtMap[$evtName]);    
        } 
    }

    public function bind($evtName, \Closure $callback) {
        $this->register($evtName);

        $this->evtMap[$evtName][] = $callback;
    }

    public function unbind($evtName, \Closure $callback) {
        if ( !isset($this->evtMap[$evtName]) || !$this->evtMap[$evtName] ) {
            return false;    
        } 

        foreach ($this->evtMap[$evtName] as $key => $evt) {
            if( $evt == $callback ) {
                unset($this->evtMap[$evtName][$key]);
                return true;
            }
        }
        return false;
    }

    public function fire($evtName, $args=null) {
        if ( isset($this->evtMap[$evtName]) && $this->evtMap[$evtName] ) {
            foreach ($this->evtMap[$evtName] as $evt) {
                call_user_func($evt, $args);
            }
        }

        $this->evtChain->fireEventChain($evtName);
    }
}
