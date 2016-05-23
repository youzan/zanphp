<?php
/**
 * Created by PhpStorm.
 * User: haitao
 * Date: 16/3/16
 * Time: 17:04
 */

namespace Zan\Framework\Sdk\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class FileLogger extends BaseLogger implements LoggerInterface {

    public function __construct($config){
        $this->init($config);
        $this->writer = new FileWriter($this->app, $this->module, $this->type, $this->path);
    }



} 