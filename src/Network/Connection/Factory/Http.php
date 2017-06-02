<?php


namespace Zan\Framework\Network\Connection\Factory;

use Zan\Framework\Contract\Network\ConnectionFactory;

class Http implements ConnectionFactory
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