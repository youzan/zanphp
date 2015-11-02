<?php

class Event {
    private static $evtMap  = [];
    private static $afterMap= [];

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

    public static function bind($evtName, callable $callback) {
        self::register($evtName);

        self::$evtMap[$evtName][] = $callback;
    }

    public static function unbind($evtName, callable $callback) {
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
        
        foreach (self::$evtMap[$evtName] as $key => $evt) {
            call_user_func($evt);
        }

        self::executeAfterEvent($evtName);

        return true;
    }

    public static function after($evtName, callable $callback) {
        if (!isset(self::$afterMap[$evtName])) {
            self::$afterMap[$evtName] = [ $callback ]; 
            return true;
        }  

        self::$afterMap[$evtName][] = $callback; 
    }


    private static function executeAfterEvent($evtName) {
        if (!isset( self::$afterMap[$evtName]) || !self::$afterMap[$evtName] ) {
            return false;    
        } 
        
        foreach (self::$afterMap[$evtName] as $key => $evt) {
            call_user_func($evt);
        }
        
        return true;
    }

}
