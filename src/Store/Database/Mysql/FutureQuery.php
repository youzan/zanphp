<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;
use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Store\Database\Mysql\QueryExecuter;
class FutureQuery implements Async
{
    private $callback;
    private $executer;

    public function __construct(QueryExecuter $executer)
    {
        $this->executer = $executer;
    }

    public function execute(callable $callback)
    {
        $this->callback = $callback;
        $this->call();
    }

    private function call()
    {
        $data = $this->executer->send();
        call_user_func($this->callback, $data);
    }


}