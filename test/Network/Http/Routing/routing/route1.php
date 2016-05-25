<?php

return [

    'prefix'       => '',

    'rewrite'      => [
        '/detail/:order_no/:kdt_id'   => 'order/book/detail',
        '/order/book$'                => 'order/book/index',
    ],
];