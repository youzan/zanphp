<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/3/11
 * Time: 10:23
 */

namespace Zan\Framework\Network\Common;


use Zan\Framework\Foundation\Contract\Async;

class RedisResult implements  Async{

    private $callback = null;

    public function execute(callable $callback){
        var_dump('execute:');
        $this->callback = $callback;
    }

    public function response($data)
    {

        var_dump('respons' ,$data);
        call_user_func($this->callback, $data);
    }
}