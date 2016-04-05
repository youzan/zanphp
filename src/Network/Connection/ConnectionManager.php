<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/1
 * Time: 18:12
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\Engine\FutureConnection;

class ConnectionManager
{
    private static $poolMap = [];
    
    public static function init()
    {
        
    }

    /**
     * @param string $connKey
     * @param int $timeout
     * @return \Zan\Framework\Contract\Network\Connection 
     * @throws InvalidArgumentException
     */
    public static function get($connKey, $timeout=0)
    {
        if(!isset(self::$poolMap[$connKey])){
            throw new InvalidArgumentException('No such ConnectionPool:'. $connKey);
        }     
        
        $pool = &self::$poolMap[$connKey];
        
        $connection = $pool->get();
        if($connection){
            yield $connection;
        }
        
        yield new FutureConnection($connKey, $timeout); 
    }
}