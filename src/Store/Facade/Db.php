<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 14:46
 */

namespace Zan\Framework\Store\Facade;

use Zan\Framework\Store\Database\Flow;

class Db {
    const RETURN_AFFECTED_ROWS  = true;
    const USE_MASTER            = true;
    const RETURN_INSERT_ID      = false;
    
    public static function execute($sid, $data, $options = [])
    {
        $flow = new Flow();
        yield $flow->query($sid, $data, $options);
        return;
    }
 
    public static function beginTransaction()
    {
        $flow = new Flow();
        yield $flow->beginTransaction();
    }
    
    public static function commit()
    {
        $flow = new Flow();
        yield $flow->commit();
    }
    
    public static function rollback()
    {
        $flow = new Flow();
        yield $flow->rollback();
    }
}