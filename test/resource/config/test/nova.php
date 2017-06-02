<?php

return [
    'server' => [
        'daemonize' => 0,
        'worker_num' => 2,
        'max_request' => 100000,
        'reactor_num' => 2,
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 0,
        'open_nova_protocol' => 1,
        'package_max_length' => 2000000
    ],
//    'platform' => [
//        'enable_report' => 0,
//        'hawk_url' => 'http://192.168.66.240:8188',
//        'report_interval' => 300
//    ],
    // 发布tcp demo服务
//    'novaApi' => [
//        'path'  => 'vendor/zanphp/novatcpdemo/gen-php',
//        'namespace' => 'Com\\Youzan\\NovaTcpDemo\\',
//    ],

    // 测试泛化
//    'novaApi' => [
//        'path'  => 'vendor/nova-service/generic-test/sdk/gen-php',
//        'namespace' => 'Com\\Youzan\\Nova\\',
//    ],








    'novaApi' => [
        [
//            'path'  => 'vendor/zanphp/novatcpdemo/gen-php',
//          'namespace' => 'Com\\Youzan\\NovaTcpDemo\\',
//            'path'  => 'vendor/nova-service/scrm/gen-php',
//            'namespace' => 'Com\\Youzan\\Scrm\\',
                'path'  => 'vendor/nova-service/generic-test/sdk/gen-php',
                'namespace' => 'Com\\Youzan\\Nova\\',
        ]
    ],

//    [
//        'domain' => 'com.youzan.test',
//        'appName'   => 'biz-api',
//
//        'path'  => 'vendor/nova-service/scrm/gen-php',
//        'namespace' => 'Com\\Youzan\\Scrm\\',
//    ],
//    [
//        'domain' => 'com.youzan.test',
//        'appName'   => 'biz-api',
//
//        'path'  => 'vendor/nova-service/pf/gen-php',
//        'namespace' => 'Com\\Youzan\\Pf\\',
//    ],

//    'novaApi' => [
//
//        'domain'    => 'com.youzan.XXX',
//        'appName'   => 'servx-app',
//        'namespace' => 'Com\\Youzan\\Servx\\',
//        'path'  => 'vendor/nova-service/servx/gen-php',

        /*

        // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        // http://etcd-dev.s.qima-inc.com:2379/v2/keys/nova:com.youzan.service/servx-app
        [
            'domain' => 'com.youzan.service',
            'appName'   => 'servx-app',

            'namespace' => 'Com\\Youzan\\Servx\\',
            'path'  => 'vendor/nova-service/servx/gen-php',

        ],
        [
            'domain' => 'com.youzan.service',
            'appName'   => 'servx-app',

            'path'  => 'vendor/nova-service/servy/gen-php',
            'namespace' => 'Com\\Youzan\\Servy\\',
        ],

        // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        // http://etcd-dev.s.qima-inc.com:2379/v2/keys/nova:com.youzan.service/servy-app
        [
            'domain' => 'com.youzan.service',
            'appName'   => 'servy-app',

            'path'  => 'vendor/nova-service/servx/gen-php',
            'namespace' => 'Com\\Youzan\\Servx\\',
        ],
        [
            'domain' => 'com.youzan.service',
            'appName'   => 'servy-app',

            'path'  => 'vendor/nova-service/servy/gen-php',
            'namespace' => 'Com\\Youzan\\Servy\\',
        ],



        // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        // http://etcd-dev.s.qima-inc.com:2379/v2/keys/nova:com.youzan.test/servx-app
        [
            'domain' => 'com.youzan.test',
            'appName'   => 'servx-app',

            'path'  => 'vendor/nova-service/servx/gen-php',
            'namespace' => 'Com\\Youzan\\Servx\\',
        ],
        [
            'domain' => 'com.youzan.test',
            'appName'   => 'servx-app',

            'path'  => 'vendor/nova-service/servy/gen-php',
            'namespace' => 'Com\\Youzan\\Servy\\',
        ],


        // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        // http://etcd-dev.s.qima-inc.com:2379/v2/keys/nova:com.youzan.test/servy-app
        [
            'domain' => 'com.youzan.test',
            'appName'   => 'servy-app',

            'path'  => 'vendor/nova-service/servx/gen-php',
            'namespace' => 'Com\\Youzan\\Servx\\',
        ],
        [
            'domain' => 'com.youzan.test',
            'appName'   => 'servy-app',

            'path'  => 'vendor/nova-service/servy/gen-php',
            'namespace' => 'Com\\Youzan\\Servy\\',
        ],
        */
//    ],
];
