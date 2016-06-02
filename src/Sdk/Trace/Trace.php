<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/31
 * Time: 下午4:54
 */

namespace Zan\Framework\Sdk\Trace;

class Trace
{
    private $run;
    private $config;

    private static $_queue;
    private static $_instance = null;

    public static function getInstance($config)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

    public function __construct($config)
    {
        $this->run = false;
        
        if ($config) {
            $this->config = $config;
        }

        if (isset($config['run']) && $config['run'] == true) {
            $this->run = true;
        }
    }
}