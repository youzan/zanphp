<?php

/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 13:03
 */

namespace Zan\Framework\Network\Connection\Factory;

use Zan\Framework\Contract\Network\ConnectionFactory;

class Syslog implements ConnectionFactory
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function create()
    {
        
    }

    public function close()
    {

    }

    public function heart()
    {
    }
}