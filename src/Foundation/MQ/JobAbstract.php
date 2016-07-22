<?php
namespace Zan\Framework\Foundation\MQ;

use Kdt\Iron\NSQ\Message\Msg;
use Zan\Framework\Sdk\Queue\NSQ\Queue;

abstract class JobAbstract
{
    protected static $name = '';

    protected static $keyFormat = '';

    /** @var  Msg */
    protected $msg;
    
    /** @var  array */
    protected $data;

    protected $beginTime;
    protected $endTime;

    final public static function makePayload(array $data)
    {
        return [
            'job_name' => static::$name,
            'job_key' => static::makeKey($data),
            'job_data' => $data,
            'job_time' => date('Y-m-d H:i:s'),
        ];
    }
    
    abstract public function checkMsg();
    
    abstract public function handleMsgError();

    final protected function checkData()
    {
        $jobData = $this->msg->data();

        if (empty($jobData) ||
            !is_array($jobData)
            || !isset($jobData['job_name'])
            || !isset($jobData['job_key'])
            || !isset($jobData['job_time'])
            || !isset($jobData['job_data'])) {
            return false;
        }
        if (static::$name != $jobData['job_name']) {
            return false;
        } elseif (static::makeKey($jobData['job_data']) != $jobData['job_key']) {
            return false;
        }

        $this->data = $jobData['job_data'];

        return true;
    }

    protected static function makeKey(array $data)
    {
        return '';
    }
    
    final public function fire()
    {
        try {
            $this->begin();
            yield $this->doFiring();
            $this->end();
        } catch (\Exception $e) {
            //todo
        }
    }
    
    final public function begin()
    {
        $this->beginTime = microtime(true);
    }

    abstract public function doFiring();
    
    final public function end()
    {
        $this->endTime = microtime(true);
    }

    final public function setMsg(Msg $msg)
    {
        $this->msg = $msg;
        $this->data = $msg->data();
    }

    final protected function getData()
    {
        return $this->data;
    }

    final public function ack()
    {
        if ($this->msg instanceof Msg) {
            $this->msg->done();
        }
    }

    final public function retry()
    {
        if ($this->msg instanceof Msg) {
            $this->msg->retry();
        }
    }

    final public function delay($seconds)
    {
        if ($this->msg instanceof Msg) {
            $this->msg->delay($seconds);
        }
    }
    
    final public static function publish($abstract, $data)
    {
        $payload = static::makePayload($data);
        yield (new Queue())->publish($abstract::TOPIC, Msg::fromClient($payload));
    }

    final public static function isOk($result)
    {
        if (isset($result['result']) and $result['result'] === 'ok') {
            return true;
        }

        return false;
    }
}