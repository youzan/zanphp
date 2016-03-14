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

class SelectJob extends Job
{
    private $sid = '';

    private $data = [];

    private $options = [];

    public function setSid($sid)
    {
        $this->sid = $sid;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function run()
    {
        $response = (yield Db::executer($this->sid, $this->data, $this->options));
//        $this->context->set('response', $response);
        print_r($response);exit;
        yield $response;
    }
}