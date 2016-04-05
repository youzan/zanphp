<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 16/3/15
 * Time: 14:14
 */

namespace Zan\Framework\Test\Sdk\Log;

use Zan\Framework\Sdk\Log\LoggerFactory;

class LogClient {

    private $logger;

    public function __construct(){
        $this->logger = LoggerFactory::getLogger('zanhttdemo');
    }

    public function addLog($msg)
    {
        yield $this->logger->info($msg);
    }
} 