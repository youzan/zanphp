<?php

return [

    'prefix'       => '',

    'rewrite'      => [
        '/detail/:order_no/:kdt_id'   => 'order/book/detail',
        '/refund/safe'                => 'refund/safe/index',
    ],
];