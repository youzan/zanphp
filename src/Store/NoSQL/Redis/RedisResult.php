<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/3/11
 * Time: 10:23
 */

namespace Zan\Framework\Store\NoSQL\Redis;


use Zan\Framework\Foundation\Contract\Async;

class RedisResult implements  Async{

    private $callback = null;

    public function execute(callable $callback){
        $this->callback = $callback;
    }

    public function response($data, $status)
    {
        call_user_func($this->callback, $data);
    }
}