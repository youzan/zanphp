<?php
namespace Zan\Framework\Foundation\Coroutine;

class Event
{
    private $evtMap = [];
    private $evtChain = null;

    const NORMAL_EVENT = 1;
    const ONCE_EVENT = 2;

    public function __construct()
    {
        $this->evtMap = [];
        $this->evtChain = new EventChain($this);
    }

    public function getEventChain()
    {
        return $this->evtChain;
    }

    public function register($evtName)
    {
        if (!isset($this->evtMap[$evtName])) {
            $this->evtMap[$evtName] = [];
        }
    }

    public function unregister($evtName)
    {
        if (isset($this->evtMap[$evtName])) {
            unset($this->evtMap[$evtName]);
        }
    }

    public function once($evtName, callable $callback)
    {
        return $this->bind($evtName, $callback, Event::ONCE_EVENT);
    }

    public function bind($evtName, callable $callback, $evtType=Event::NORMAL_EVENT)
    {
        $this->register($evtName);

        $this->evtMap[$evtName][] = [
            'callback'  => $callback,
            'evtType'   => $evtType,
        ];
    }

    public function unbind($evtName, callable $callback)
    {
        if (!isset($this->evtMap[$evtName]) || !$this->evtMap[$evtName]) {
            return false;
        }

        foreach ($this->evtMap[$evtName] as $key => $evt) {
            $cb = $evt['callback'];
            if ($cb == $callback) {
                unset($this->evtMap[$evtName][$key]);
                return true;
            }
        }
        return false;
    }

    public function fire($evtName, $args=null, $loop=true)
    {
        if (isset($this->evtMap[$evtName]) && $this->evtMap[$evtName]) {
            $this->fireEvents($evtName, $args, $loop);
        }

        $this->evtChain->fireEventChain($evtName);
    }
    
    private function fireEvents($evtName, $args=null, $loop=true)
    {
        foreach ($this->evtMap[$evtName] as $key => $evt) {
            $callback = $evt['callback'];
            $evtType = $evt['evtType'];
            call_user_func($callback, $args);

            if (Event::ONCE_EVENT === $evtType) {
                unset($this->evtMap[$evtName][$key]);
            }

            if(false === $loop){
                break;
            }
        }
    }
}
