<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/8
 * Time: 下午3:58
 */
namespace Zan\Framework\Test\Store\Database\Sql\Task;
use Zan\Framework\Store\Facade\Db;
use Zan\Framework\Test\Foundation\Coroutine\Task\Job;

class InsertJob extends Job
{
    private $sid = '';

    private $insert = [];

    private $options = [];

    public function setSid($sid)
    {
        $this->sid = $sid;
        return $this;
    }

    public function setInsert($insert)
    {
        $this->insert = $insert;
        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function run()
    {
        $response = (yield Db::executer($this->sid, $this->insert, $this->options));
        $this->context->set('response', $response);
        yield $response;
    }
}