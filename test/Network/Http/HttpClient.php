<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Http\Client;

class HttpClient {

    protected $context = null;

    public function __construct($context) {

        $this->context = $context;
    }

    private function getOrder($orderNo, $kdtId)
    {
        $option = [
            'order_no'     => $orderNo,
            'kdt_id'       => $kdtId,
            'format_order' => false,
            'with_items'   => false,
            'with_peerpay' => false,
            'with_source'  => false
        ];

        yield Client::call('trade.order.detail.byOrderNo', $option);
    }

    public function call()
    {
        $that = $this;
        $result = (yield ($this->getOrder('E123', 1)) );

        $this->context->set('key', $result);
        var_dump($this, time());exit;

        yield $result;
        //swoole_event_exit();
    }
}