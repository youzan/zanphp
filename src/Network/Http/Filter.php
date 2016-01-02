<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;

class Filter extends \Zan\Framework\Network\Contract\Filter {

    private $config = [];

    public function __construct() {
        $this->config = Config::get('filter');
    }

    public function preFilter() {
        //根据config里的顺序，如：先ACL,在其他...
        //读取resource/preFilter目录下的所有filter
    }

    public function postFilter() {
        //根据config里的顺序，如：先ACL,在其他...
        //读取resource/postFilter目录下的所有filter
    }
}