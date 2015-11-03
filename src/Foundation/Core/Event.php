<?php
namespace Zan\Framework\Foundation\Core;

class Event {
    private static $evtMap  = [];
    private static $afterMap= [];

    public static function clear() {
        self::$evtMap = [];
        self::$afterMap = [];
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
        if (!isset( self::$evtMap[$evtName]) || !self::$evtMap[$evtName] ) {
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

    public static function fire($evtName) {
        if (!isset( self::$evtMap[$evtName]) || !self::$evtMap[$evtName] ) {
            return false;    
        } 
        
        foreach (self::$evtMap[$evtName] as $evt) {
            call_user_func($evt);
        }

        self::executeAfterEvent($evtName);
    }

    public static function after($beforeEvt, $evtName) {
        if (!isset(self::$afterMap[$beforeEvt])) {
            self::$afterMap[$beforeEvt] = [ $evtName ];
            return true;
        }  

        self::$afterMap[$beforeEvt][] = $evtName;
    }

    public static function unafter($beforeEvt, $evtName) {
        if (!isset( self::$afterMap[$beforeEvt]) || !self::$afterMap[$beforeEvt] ) {
            return false;
        }

        foreach (self::$afterMap[$beforeEvt] as $key => $evt) {
            if( $evt == $evtName ) {
                unset(self::$afterMap[$beforeEvt][$key]);
                return true;
            }
        }

        return false;
    }

    private static function executeAfterEvent($evtName) {
        if (!isset( self::$afterMap[$evtName]) || !self::$afterMap[$evtName] ) {
            return false;    
        } 
        
        foreach (self::$afterMap[$evtName] as $evtName) {
            self::fire($evtName);
        }
    }

}
