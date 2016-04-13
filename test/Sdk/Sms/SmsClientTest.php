<?php

require __DIR__ . '/../../bootstrap.php';

//namespace Zan\Framework\Test\Sdk\Sms;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Sdk\Sms\Channel;
use Zan\Framework\Sdk\Sms\MessageContext;
use Zan\Framework\Sdk\Sms\Recipient;
use Zan\Framework\Sdk\Sms\SmsService;


class SmsClientTest extends \TestCase
{
    public function testSearch()
    {

        $context = new Context();

        $coroutine = $this->wahaha();

        $task = new Task($coroutine, $context, 19);
        $task->run();

    }

    private function wahaha()
    {
        $param = array(
            'goodsTitle' => 'aaaaa',
            'reason' => 'hahaha',
        );

        $result = (yield SmsService::getInstance()->send(
            new MessageContext('fenxiaoRevokeBestGoods', $param),
            [new Recipient(Channel::SMS, 15757110811)]
        ));

        var_dump($result);
    }


}

(new SmsClientTest())->testSearch();