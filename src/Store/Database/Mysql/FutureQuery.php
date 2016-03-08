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
    private $callback;

    public function __construct()
    {

    }


    public function execute(callable $callback)
    {
        
    }
}