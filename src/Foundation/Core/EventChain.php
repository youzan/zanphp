<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/11
 * Time: 18:47
 */

namespace Zan\Framework\Foundation\Core;


class EventChain {
    private static $beforeMap = [];
    private static $afterMap = [];

    public static function clear() {
        self::$beforeMap = [];
        self::$afterMap = [];
    }

    public static function join() {
        $argNum = func_num_args();
        if($argNum < 2){
            return false;
        }
        $args = func_get_args();

        $beforeEvt  = null;
        $afterEvt   = null;
        foreach($args as $evt) {
            if (null === $beforeEvt) {
                $beforeEvt = $evt;
                continue;
            }

            $afterEvt = $evt;
            self::after($beforeEvt,$afterEvt);
            $beforeEvt = $afterEvt;
        }
    }

    public static function crack($beforeEvt, $afterEvt) {
        if (!isset(self::$afterMap[$beforeEvt])) {
            return false;
        }

        if (!isset(self::$afterMap[$beforeEvt][$afterEvt])) {
            return false;
        }

        unset(self::$afterMap[$beforeEvt][$afterEvt]);
        return true;
    }

    public static function after($beforeEvt, $afterEvt) {
        if (!isset(self::$afterMap[$beforeEvt])) {
            self::$afterMap[$beforeEvt] = [ $afterEvt => 1 ];
            return true;
        }
        self::$afterMap[$beforeEvt][$afterEvt] = 1;
    }

    public static function before($beforeEvt, $afterEvt) {
        self::after($beforeEvt, $afterEvt);
        if (!isset(self::$beforeMap[$afterEvt])) {
            self::$beforeMap[$afterEvt] = [ $beforeEvt => 0 ];
            return true;
        }

        self::$beforeMap[$afterEvt][$beforeEvt] = 0;
    }

    public static function fireEventChain($evtName) {
        if(!isset(self::$afterMap[$evtName])){
            return false;
        }

        $afterEvt = self::$afterMap[$evtName];
        self::fireEvent($evtName, $afterEvt);

        if(true !== self::isBeforeEventFired($evtName)){
            return false;
        }

        Event::fire($afterEvt);
        self::clearBeforeEventBind($afterEvt);
        return true;
    }

    private static function fireEvent($beforeEvt, $afterEvt) {
        if(!isset(self::$beforeMap[$afterEvt][$beforeEvt])) {
            return false;
        }
        self::$beforeMap[$afterEvt][$beforeEvt]++;
    }

    private static function clearBeforeEventBind($afterEvt) {
        if(!isset(self::$beforeMap[$afterEvt])){
            return false;
        }

        $decrease = function(&$v) {
            return $v--;
        };
        array_walk(self::$beforeMap[$afterEvt], $decrease);
    }

    private static function isBeforeEventFired($afterEvt) {
        if(!isset(self::$beforeMap[$afterEvt])){
            return true;
        }

        foreach(self::$beforeMap[$afterEvt] as $count){
            if($count < 1) {
                return false;
            }
        }

        return true;
    }

}