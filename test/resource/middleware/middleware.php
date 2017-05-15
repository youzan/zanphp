<?php

return [
    'group'     => [
        'web'   => [
            'Acl','Auth'
        ],
        'trade' =>[
            'Acl','Auth','Trade'
        ]
    ],
    'match'     => [
        //The sequence is important, generic match is behind specific match
        ['/trade/order/.*',  'trade'],
        ['/trade/.*',  'web'],
    ],
];
