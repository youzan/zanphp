<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/3
 * Time: 23:59
 */

return [
    'extends'           => '',

    'prefix'            => 'urlPrefix',
    'domain'            => [
        'shop{:kdt_id}.youzan.com'  => '/showcase/home/:kdt_id',
    ],

    'middleware_group'  => [
        'web'           => [

        ],
        'api'           => [

        ],
    ],

    'middleware'        => [
        '/trade'        => [
            'group'     => ['web','api'],
            'middleware'=> ['','',''],
        ]
    ],



    'rewrite'           => [
        'GET /:module/xxx/yyy'  => '/:module/index/index'
    ],

    'tiny_url_switch'   => true,
    'tiny_url_rule'     => [

    ],

];