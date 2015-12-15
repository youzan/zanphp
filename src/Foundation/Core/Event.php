<?php
namespace Zan\Framework\Foundation\Core;

class Event {
    private static $evtMap  = [];

    const NORMAL_EVENT = 1;
    const ONCE_EVENT = 2;

    public static function clear() {
        self::$evtMap = [];
        EventChain::clear();
    }

    public static function register($evtName) {
        if (!isset(self::$evtMap[$evtName])) {
            self::$evtMap[$evtName] = []; 
        } 
    }

    public static function unregister($evtName) {
        if (isset(self::$evtMap[$evtName])) {
            unset(self::$evtMap[$evtName]);    
        } 
    }

    public static function once($evtName, \Closure $callback)
    {
        self::register($evtName);

        self::$evtMap[$evtName][] = [
            'callback' => $callback,
            'evtType' => Event::ONCE_EVENT,
        ];
    }

    public static function bind($evtName, \Closure $callback) {
        self::register($evtName);

        self::$evtMap[$evtName][] = [
            'callback' => $callback,
            'evtType' => Event::NORMAL_EVENT,
        ];
    }

    public static function unbind($evtName, \Closure $callback) {
        if ( !isset(self::$evtMap[$evtName]) || !self::$evtMap[$evtName] ) {
            return false;    
        } 

        foreach (self::$evtMap[$evtName] as $key => $evt) {
            $cb = $evt['callback'];
            if( $cb == $callback ) {
                unset(self::$evtMap[$evtName][$key]);
                return true;
            }
        }
        return false;
    }

    public static function fire($evtName, $args=null) {
        if ( isset(self::$evtMap[$evtName]) && self::$evtMap[$evtName] ) {
            foreach (self::$evtMap[$evtName] as $key => $evt) {
                $callback = $evt['callback'];
                $evtType  = $evt['evtType'];
                call_user_func($callback, $args);

                if(Event::ONCE_EVENT === $evtType) {
                    unset(self::$evtMap[$evtName][$key]);
                }
            }
        }

        EventChain::fireEventChain($evtName);
    }
}
