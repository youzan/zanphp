<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http;


use Zan\Framework\Network\Common\Client;

class HttpClient {

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
        yield ($this->getOrder('E123', 1));
    }
}