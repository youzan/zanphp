<?php
namespace Zan\Framework\Foundation\Coroutine;

class EventChain
{
    private $beforeMap = [];
    private $afterMap = [];
    private $event = null;

    public function __construct(Event $event)
    {
        $this->beforeMap = [];
        $this->afterMap = [];
        $this->event = $event;
    }

    /**
     * 连接N个传入的事件为事件链
     * @param args
     * @return bool
     */
    public function join()
    {
        $argNum = func_num_args();
        if ($argNum < 2) {
            return false;
        }
        $args = func_get_args();

        $beforeEvt = null;
        $afterEvt = null;
        foreach ($args as $evt) {
            if (null === $beforeEvt) {
                $beforeEvt = $evt;
                continue;
            }

            $afterEvt = $evt;
            $this->after($beforeEvt, $afterEvt);
            $beforeEvt = $afterEvt;
        }
    }

    /**
     * 断开两个事件链接
     * @param $beforeEvt
     * @param $afterEvt
     */
    public function breakChain($beforeEvt, $afterEvt)
    {
        $this->crackAfterChain($beforeEvt, $afterEvt);
        $this->crackBeforeChain($beforeEvt, $afterEvt);
    }

    public function after($beforeEvt, $afterEvt)
    {
        if (!isset($this->afterMap[$beforeEvt])) {
            $this->afterMap[$beforeEvt] = [$afterEvt => 1];
            return true;
        }
        $this->afterMap[$beforeEvt][$afterEvt] = 1;
    }

    public function before($beforeEvt, $afterEvt)
    {
        $this->after($beforeEvt, $afterEvt);
        if (!isset($this->beforeMap[$afterEvt])) {
            $this->beforeMap[$afterEvt] = [$beforeEvt => 0];
            return true;
        }

        $this->beforeMap[$afterEvt][$beforeEvt] = 0;
    }

    public function fireEventChain($evtName)
    {
        if (!isset($this->afterMap[$evtName]) || !$this->afterMap[$evtName]) {
            return false;
        }

        foreach ($this->afterMap[$evtName] as $afterEvt => $count) {
            $this->fireAfterEvent($evtName, $afterEvt);
        }

        return true;
    }

    private function fireAfterEvent($beforeEvt, $afterEvt)
    {
        $this->fireBeforeEvent($beforeEvt, $afterEvt);

        if (true !== $this->isBeforeEventFired($afterEvt)) {
            return false;
        }

        $this->clearBeforeEventBind($afterEvt);
        $this->event->fire($afterEvt);
    }

    private function fireBeforeEvent($beforeEvt, $afterEvt)
    {
        if (!isset($this->beforeMap[$afterEvt])) {
            return false;
        }

        if (!isset($this->beforeMap[$afterEvt][$beforeEvt])) {
            return false;
        }
        $this->beforeMap[$afterEvt][$beforeEvt]++;
    }

    private function clearBeforeEventBind($afterEvt)
    {
        if (!isset($this->beforeMap[$afterEvt])) {
            return false;
        }

        $decrease = function (&$v) {
            return $v--;
        };
        array_walk($this->beforeMap[$afterEvt], $decrease);
    }

    private function isBeforeEventFired($afterEvt)
    {
        if (!isset($this->beforeMap[$afterEvt])) {
            return true;
        }

        foreach ($this->beforeMap[$afterEvt] as $count) {
            if ($count < 1) {
                return false;
            }
        }

        return true;
    }

    private function crackAfterChain($beforeEvt, $afterEvt)
    {
        if (!isset($this->afterMap[$beforeEvt])) {
            return false;
        }

        if (!isset($this->afterMap[$beforeEvt][$afterEvt])) {
            return false;
        }

        unset($this->afterMap[$beforeEvt][$afterEvt]);
        return true;
    }

    private function crackBeforeChain($beforeEvt, $afterEvt)
    {
        if (!isset($this->beforeMap[$afterEvt])) {
            return false;
        }

        if (!isset($this->beforeMap[$afterEvt][$beforeEvt])) {
            return false;
        }

        unset($this->beforeMap[$afterEvt][$beforeEvt]);
        return true;
    }
}