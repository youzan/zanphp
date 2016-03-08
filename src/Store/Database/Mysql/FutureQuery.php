<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;
use Zan\Framework\Foundation\Contract\Async;
class FutureQuery implements Async
{
    public function __construct($result)
    {
        $this->init($result);
    }

    private function init(\mysqli_result $result)
    {
        print_r($result->fetch_all());
    }

    public function execute(callable $callback)
    {

    }
}