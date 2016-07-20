<?php

namespace Zan\Framework\Sdk\Sms;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Common\Client;

class SmsService
{
    use Singleton;

    /**
     * @param MessageContext $messageContext
     * @param Recipient[]    $recipients
     *
     * @return bool
     */
    public function send(MessageContext $messageContext, array $recipients)
    {
        if (!$messageContext instanceof MessageContext) {
            yield false;
        }

        $params = [];

        $msgCxt = [
            'templateName' => $messageContext->getTemplateName(),
            'paramMap' => $messageContext->getParams()
        ];
        $params['messageContext'] = $msgCxt;

        $recipientRequests = array();
        foreach ($recipients as $recipient) {
            if ($recipient instanceof Recipient) {
                $rcpt = array();
                $rcpt['sender'] = $recipient->getSender();
                $rcpt['receiver'] = $recipient->getReceiver();
                $rcpt['channel'] = $recipient->getChannel();
                $recipientRequests[] = $rcpt;
            }
        }
        $params['recipientRequests'] = $recipientRequests;

        yield Client::call("courier.push.sendMessage", $params, 'POST');
    }

}