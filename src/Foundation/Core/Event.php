<?php
namespace Zan\Framework\Foundation\Core;

class Event {
    private static $evtMap  = [];

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

    public static function bind($evtName, \Closure $callback) {
        self::register($evtName);

        self::$evtMap[$evtName][] = $callback;
    }

    public static function unbind($evtName, \Closure $callback) {
        if ( !isset(self::$evtMap[$evtName]) || !self::$evtMap[$evtName] ) {
            return false;    
        } 

        foreach (self::$evtMap[$evtName] as $key => $evt) {
            if( $evt == $callback ) {
                unset(self::$evtMap[$evtName][$key]);
                return true;
            }
        }
        return false;
    }

    public static function fire($evtName, $args=null) {
        if ( isset(self::$evtMap[$evtName]) && self::$evtMap[$evtName] ) {
            foreach (self::$evtMap[$evtName] as $evt) {
                call_user_func($evt, $args);
            }
        }
        
        EventChain::fireEventChain($evtName);
    }
}
