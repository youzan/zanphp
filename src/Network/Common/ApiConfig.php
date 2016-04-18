<?php

return [
    'dev' => [
        'account' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com:80',
            'sub' => [
                [
                    'mod' => 'account.admin',
                    'host' => '192.168.66.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.admin.createBbsAccount',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.saveRegistCode',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getRegistCodeInfo',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getListWithTeam',// WAIT: INNER CALL
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.checkLogin',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.checkMobileCode',// WAIT: INNER CALL
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.updateBbsPassword',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.updateBbsNickName',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.updateBbsAccount',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getBbsUidByAdminId',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.platformUser',
                    'host' => '192.168.66.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userRead',
                    'host' => '192.168.66.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userWrite',
                    'host' => '192.168.66.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserRead',
                    'host' => '192.168.66.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserWrite',
                    'host' => '192.168.66.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagRead',
                    'host' => '192.168.66.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagWrite',
                    'host' => '192.168.66.207:8025',
                    'type' => 'java'
                ]
            ]

        ],
        'feeds'  => [
            'type' => 'java',
            'host' => '10.6.7.95:8025',
        ],
        'verifier' => [
            'type' => 'java',
            'host' => 'verifier.youzan.com:8090',
            'sub' => [
                [
                    'mod' => 'verifier.verifier',
                    'host' => 'verifier.youzan.com:8090',
                    'type' => 'java'
                ]
            ]
        ],
        'courier' => [
            'type' => 'java',
            'host' => '192.168.66.204:8011'//预发 10.10.72.5
        ],
        'courierold' => [
            'type' => 'java',
            'host' => '192.168.66.204:8019'//预发 10.10.72.5
        ],
        'trade' => [
            'type' => 'php',
            'host' => 'trade.api.youzan.com',
            'sub'  => [
                [
                    'mod' => 'trade.buy.bookByParams',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierBook',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.updateAddressByParams',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierUpdateBuyWay',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.confirmByParams',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.afterPayService.processNormalOrderPaySuccess',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeBook',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateBuyWay',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateAddress',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.tm.query',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStockService.decrease',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStateService.modifyWaitToSend',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.create',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.confirm',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.getCode',
                    'host' => '192.168.66.206:8025',
                    'type' => 'java'
                ],
            ]
        ],
        'timeout' => [
            'type' => 'php',
            'host' => 'trade.api.youzan.com',
            'sub'  => [
                [
                    'mod' => 'timeout.order.delivered.delay.increase',
                    'host' => '127.0.0.1:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'timeout.order.delivered.delay.getEndTime',
                    'host' => '127.0.0.1:8025',
                    'type' => 'java'
                ]
            ]
        ],
        'ump' => [
            'type' => 'php',
            'host' => 'ump.api.youzan.com'
        ],
        'goods' => [
            'type' => 'php',
            'host' => 'goods.api.youzan.com'
        ],
        'showcase' => [
            'type' => 'php',
            'host' => 'showcase.api.youzan.com'
        ],
        'fenxiao' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'sinaweibo' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'showcase' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'pay' => [
            'type' => 'php',
            'host' => 'pay.api.youzan.com',
            'sub' => [
                [
                    'mod' => 'pay.yzpay',
                    'host' => '172.16.4.102:8038',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.checkstand',
                    //'host' => '192.168.66.238:8048',
                    'host' => '172.17.8.254:8048',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.ticket',
                    'host' => '192.168.66.238:8088',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.settlement',
                    'host' => '192.168.66.238:8138',
                    'type' => 'java'
                ],
            ]
        ],
        'funds' => [
            'type' => 'php',
            'host' => 'pay.api.youzan.com',
            'sub' => [
                [
                    'mod' => 'funds.wait_settled',
                    'host' => '10.200.175.232:5061/online',
                    'type' => 'java'
                ]
            ]
        ],
        'notice' => [
            'type' => 'php',
            'host' => 'notice.api.koudaitong.com'
        ],
        'fans' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'cp' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'fuwu' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'datacenter' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'common' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'weixin' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'pinjian' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'apps' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'activity' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'open' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'mock' => [
            'type' => 'yar',
            'host' => 'mock.api.koudaitong.com'
        ],
    ],

    'pre' => [
        'account' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com:80',
            'sub' => [
                [
                    'mod' => 'account.admin',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUser',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userRead',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userWrite',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserRead',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserWrite',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagRead',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagWrite',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.admin.getPersonalInfo',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.createBbsAccount',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.saveRegistCode',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getRegistCodeInfo',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getListWithTeam',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.checkLogin',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.checkMobileCode',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.updateBbsPassword',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.updateBbsNickName',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.updateBbsAccount',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getBbsUidByAdminId',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
            ]
        ],
        'feeds'  => [
            'type' => 'java',
            'host' => '10.10.180.144:8025',
        ],
        'verifier' => [
            'type' => 'php',
            'host' => '10.200.175.232:8012',
            'sub' => [
                [
                    'mod' => 'verifier.verifier',
                    'host' => '10.200.175.232:8012',
                    'type' => 'php'
                ]
            ]
        ],
        'courier' => [
            'type' => 'java',
            //					'host' > '10.10.72.5:8011'//预发 10.10.72.5
            'host' => '10.200.175.192:8011'//online bc-msg3/4/5
        ],
        'courierold' => [
            'type' => 'java',
            //                      'host' => '10.200.175.232:8011'//online bc-jvapp2/3
            'host' => '10.200.175.232:8019'//online bc-jvapp2/3 兼容的
        ],
        'trade' => [
            'type' => 'php',
            'host' => 'trade.api.youzan.com',
            'sub'  => [
                [
                    'mod' => 'trade.buy.bookByParams',
//								'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierBook',
//                                'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.updateAddressByParams',
//                                'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierUpdateBuyWay',
//								'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.confirmByParams',
//								'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStockService.decrease',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStateService.modifyWaitToSend',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.afterPayService.processNormalOrderPaySuccess',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeBook',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateBuyWay',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateAddress',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.create',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.confirm',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.getCode',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'timeout' => [
            'type' => 'php',
            'host' => 'trade.api.youzan.com',
            'sub'  => [
                [
                    'mod' => 'timeout.order.delivered.delay.increase',
//                            'host' => '10.10.12.64:8025',
                    'host' => 'timeoutcenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'timeout.order.delivered.delay.getEndTime',
                    'host' => 'timeoutcenter.s.qima-inc.com',
//                            'host' => '10.10.12.64:8025',
                    'type' => 'java'
                ]
            ]
        ],
        'ump' => [
            'type' => 'php',
            'host' => 'ump.api.youzan.com'
        ],
        'goods' => [
            'type' => 'php',
            'host' => 'goods.api.youzan.com'
        ],
        'showcase' => [
            'type' => 'php',
            'host' => 'showcase.api.youzan.com'
        ],
        'fenxiao' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'sinaweibo' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'showcase' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'pay' => [
            'type' => 'php',
            'host' => 'pay.api.youzan.com',
            'sub' => [
                [
                    'mod' => 'pay.yzpay',
                    'host' => '10.200.175.96:8038',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.checkstand',
                    'host' => '10.200.175.96',
                    //'host'  => '10.10.196.122:8048',
                    'type' => 'java',
                    'domain' => 'pay-payment.s.qima-inc.com',
                ],
                [
                    'mod' => 'pay.ticket',
                    'host' => '10.200.175.196:8398',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.settlement',
                    'host' => 'settlement.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'funds' => [
            'type' => 'php',
            'host' => 'pay.api.youzan.com',
            'sub' => [
                [
                    'mod' => 'funds.wait_settled',
                    'host' => '10.200.175.232:5061/online',
                    'type' => 'java'
                ]
            ]
        ],
        'notice' => [
            'type' => 'php',
            'host' => 'notice.api.koudaitong.com'
        ],
        'fans' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'cp' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'fuwu' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'datacenter' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'common' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'weixin' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'pinjian' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'apps' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'activity' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'open' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ]
    ],

    'online' => [
        'account' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com:80',
            'sub' => [
                [
                    'mod' => 'account.admin',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUser',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userRead',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userWrite',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserRead',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserWrite',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagRead',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagWrite',
                    'host' => 'usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.admin.getPersonalInfo',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.createBbsAccount',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.saveRegistCode',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getRegistCodeInfo',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getListWithTeam',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.checkLogin',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.checkMobileCode',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.updateBbsPassword',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.updateBbsNickName',
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.updateBbsAccount',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
                [
                    'mod' => 'account.admin.getBbsUidByAdminId',// ABORT
                    'host' => '',
                    'type' => 'php'
                ],
            ]
        ],
        'feeds'  => [
            'type' => 'java',
            'host' => '10.10.180.144:8025',
        ],
        'verifier' => [
            'type' => 'php',
            'host' => '10.200.175.232:8012',
            'sub' => [
                [
                    'mod' => 'verifier.verifier',
                    'host' => '10.200.175.232:8012',
                    'type' => 'php'
                ]
            ]
        ],
        'courier' => [
            'type' => 'java',
            //					'host' > '10.10.72.5:8011'//预发 10.10.72.5
            'host' => '10.200.175.192:8011'//online bc-msg3/4/5
        ],
        'courierold' => [
            'type' => 'java',
            //                      'host' => '10.200.175.232:8011'//online bc-jvapp2/3
            'host' => '10.200.175.232:8019'//online bc-jvapp2/3 兼容的
        ],
        'trade' => [
            'type' => 'php',
            'host' => 'trade.api.youzan.com',
            'sub'  => [
                [
                    'mod' => 'trade.buy.bookByParams',
//								'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierBook',
//                                'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.updateAddressByParams',
//                                'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierUpdateBuyWay',
//								'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.confirmByParams',
//								'host' => '10.200.175.193:8011',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStockService.decrease',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStateService.modifyWaitToSend',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.afterPayService.processNormalOrderPaySuccess',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeBook',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateBuyWay',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateAddress',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.create',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.confirm',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.getCode',
                    'host' => 'trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'timeout' => [
            'type' => 'php',
            'host' => 'trade.api.youzan.com',
            'sub'  => [
                [
                    'mod' => 'timeout.order.delivered.delay.increase',
//                            'host' => '10.10.12.64:8025',
                    'host' => 'timeoutcenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'timeout.order.delivered.delay.getEndTime',
                    'host' => 'timeoutcenter.s.qima-inc.com',
//                            'host' => '10.10.12.64:8025',
                    'type' => 'java'
                ]
            ]
        ],
        'ump' => [
            'type' => 'php',
            'host' => 'ump.api.youzan.com'
        ],
        'goods' => [
            'type' => 'php',
            'host' => 'goods.api.youzan.com'
        ],
        'showcase' => [
            'type' => 'php',
            'host' => 'showcase.api.youzan.com'
        ],
        'fenxiao' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'sinaweibo' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'showcase' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'pay' => [
            'type' => 'php',
            'host' => 'pay.api.youzan.com',
            'sub' => [
                [
                    'mod' => 'pay.yzpay',
                    'host' => '10.200.175.96:8038',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.checkstand',
                    'host' => '10.200.175.96',
                    //'host'  => '10.10.196.122:8048',
                    'type' => 'java',
                    'domain' => 'pay-payment.s.qima-inc.com',
                ],
                [
                    'mod' => 'pay.ticket',
                    'host' => '10.200.175.196:8398',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.settlement',
                    'host' => 'settlement.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'funds' => [
            'type' => 'php',
            'host' => 'pay.api.youzan.com',
            'sub' => [
                [
                    'mod' => 'funds.wait_settled',
                    'host' => '10.200.175.232:5061/online',
                    'type' => 'java'
                ]
            ]
        ],
        'notice' => [
            'type' => 'php',
            'host' => 'notice.api.koudaitong.com'
        ],
        'fans' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'cp' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'fuwu' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'datacenter' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'common' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'weixin' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'pinjian' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'apps' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'activity' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ],
        'open' => [
            'type' => 'php',
            'host' => 'api.koudaitong.com'
        ]
    ]
];