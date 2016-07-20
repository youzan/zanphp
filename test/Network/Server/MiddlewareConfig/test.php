<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/14
 * Time: 下午4:34
 */
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
        ['/\/trade\/.*/',  'web'],
        ['/\/trade\/order\/.*/',  'trade'],
    ],
];