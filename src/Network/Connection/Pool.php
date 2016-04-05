<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/4
 * Time: 01:20
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Contract\Network\ConnectionFactory;
use Zan\Framework\Contract\Network\ConnectionPool;
use Zan\Framework\Contract\Network\Connection;

class Pool implements ConnectionPool
{
    public function __construct(ConnectionFactory $connectionFactory, array $config)
    {
    }
    
    public function reload(array $config)
    {
        
    }

    public function get()
    {
        
    }
    
    public function release(Connection $conn)
    {
    }
    
    public function remove(Connection $conn)
    {
        
    }
}