<?php
namespace Zan\Framework\Foundation\Core;

class EventChain
{
    private static $beforeMap = [];
    private static $afterMap = [];

    public static function clear()
    {
        self::$beforeMap = [];
        self::$afterMap = [];
    }

    /**
     * 连接N个传入的事件为事件链
     * @param args
     * @return bool
     */
    public static function join()
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
            self::after($beforeEvt, $afterEvt);
            $beforeEvt = $afterEvt;
        }
    }

    /**
     * 断开两个事件链接
     * @param $beforeEvt
     * @param $afterEvt
     */
    public static function breakChain($beforeEvt, $afterEvt)
    {
        self::crackAfterChain($beforeEvt, $afterEvt);
        self::crackBeforeChain($beforeEvt, $afterEvt);
    }

    public static function after($beforeEvt, $afterEvt)
    {
        if (!isset(self::$afterMap[$beforeEvt])) {
            self::$afterMap[$beforeEvt] = [$afterEvt => 1];
            return true;
        }
        self::$afterMap[$beforeEvt][$afterEvt] = 1;
    }

    public static function before($beforeEvt, $afterEvt)
    {
        self::after($beforeEvt, $afterEvt);
        if (!isset(self::$beforeMap[$afterEvt])) {
            self::$beforeMap[$afterEvt] = [$beforeEvt => 0];
            return true;
        }

        self::$beforeMap[$afterEvt][$beforeEvt] = 0;
    }

    public static function fireEventChain($evtName)
    {
        if (!isset(self::$afterMap[$evtName]) || !self::$afterMap[$evtName]) {
            return false;
        }

        foreach (self::$afterMap[$evtName] as $afterEvt => $count) {
            self::fireAfterEvent($evtName, $afterEvt);
        }

        return true;
    }

    private static function fireAfterEvent($beforeEvt, $afterEvt)
    {
        self::fireBeforeEvent($beforeEvt, $afterEvt);

        if (true !== self::isBeforeEventFired($afterEvt)) {
            return false;
        }

        self::clearBeforeEventBind($afterEvt);
        Event::fire($afterEvt);
    }

    private static function fireBeforeEvent($beforeEvt, $afterEvt)
    {
        if (!isset(self::$beforeMap[$afterEvt])) {
            return false;
        }

        if (!isset(self::$beforeMap[$afterEvt][$beforeEvt])) {
            return false;
        }
        self::$beforeMap[$afterEvt][$beforeEvt]++;
    }

    private static function clearBeforeEventBind($afterEvt)
    {
        if (!isset(self::$beforeMap[$afterEvt])) {
            return false;
        }

        $decrease = function (&$v) {
            return $v--;
        };
        array_walk(self::$beforeMap[$afterEvt], $decrease);
    }

    private static function isBeforeEventFired($afterEvt)
    {
        if (!isset(self::$beforeMap[$afterEvt])) {
            return true;
        }

        foreach (self::$beforeMap[$afterEvt] as $count) {
            if ($count < 1) {
                return false;
            }
        }

        return true;
    }

    private static function crackAfterChain($beforeEvt, $afterEvt)
    {
        if (!isset(self::$afterMap[$beforeEvt])) {
            return false;
        }

        if (!isset(self::$afterMap[$beforeEvt][$afterEvt])) {
            return false;
        }

        unset(self::$afterMap[$beforeEvt][$afterEvt]);
        return true;
    }

    private static function crackBeforeChain($beforeEvt, $afterEvt)
    {
        if (!isset(self::$beforeMap[$afterEvt])) {
            return false;
        }

        if (!isset(self::$beforeMap[$afterEvt][$beforeEvt])) {
            return false;
        }

        unset(self::$beforeMap[$afterEvt][$beforeEvt]);
        return true;
    }
}