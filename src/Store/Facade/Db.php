<?php

namespace Zan\Framework\Store\Facade;

use Zan\Framework\Store\Database\Flow;

class Db
{
    const RETURN_AFFECTED_ROWS  = true;
    const USE_MASTER            = true;
    const RETURN_INSERT_ID      = false;
    
    public static function execute($sid, $data, $options = [])
    {
        $flow = new Flow();
        yield $flow->query($sid, $data, $options);
        return;
    }
 
    public static function beginTransaction($flags = 0)
    {
        $flow = new Flow();
        yield $flow->beginTransaction($flags);
    }
    
    public static function commit($flags = 0)
    {
        $flow = new Flow();
        yield $flow->commit($flags);
    }
    
    public static function rollback($flags = 0)
    {
        $flow = new Flow();
        yield $flow->rollback($flags);
    }

    public static function terminate()
    {
        $flow = new Flow();
        yield $flow->terminate();
    }
}