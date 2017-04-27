<?php
namespace Zan\Framework\Test\Sdk\Sms;

use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;
use Zan\Framework\Testing\TaskTest;
use Zan\Framework\Sdk\Sms\Channel;
use Zan\Framework\Sdk\Sms\MessageContext;
use Zan\Framework\Sdk\Sms\Recipient;
use Zan\Framework\Sdk\Sms\SmsService;

class SmsClientTest extends TaskTest
{
    public function taskSendSms()
    {
        $param = array(
            'goodsName' => '饮料',
            'realPay' => '1.5',
            'link' => "http://www.baidu.com"
        );

        try {
            $result = (yield SmsService::getInstance()->send(
                new MessageContext('virtualPaySuc', $param),
                [new Recipient(Channel::SMS, 15527999628)]
            ));
        } catch (\Exception $e) {
            $this->assertInstanceOf(HttpClientTimeoutException::class, $e);
            return;
        }

        yield $result;
    }
}