<?php
return [
    'test' => [
        'account' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com',
            'sub' => [
                [
                    'mod' => 'account.admin',
                    'host' => 'http://10.9.75.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.admin.createBbsAccount',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.saveRegistCode',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getRegistCodeInfo',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getListWithTeam',// WAIT: INNER CALL
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.checkLogin',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.checkMobileCode',// WAIT: INNER CALL
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.updateBbsPassword',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.updateBbsNickName',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.updateBbsAccount',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getBbsUidByAdminId',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getAdminIdByBbsUid',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.platformUser',
                    'host' => 'http://10.9.75.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userRead',
                    'host' => 'http://10.9.75.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userWrite',
                    'host' => 'http://10.9.75.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserRead',
                    'host' => 'http://10.9.75.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserWrite',
                    'host' => 'http://10.9.75.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagRead',
                    'host' => 'http://10.9.75.207:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagWrite',
                    'host' => 'http://10.9.75.207:8025',
                    'type' => 'java'
                ]
            ]

        ],
        'feeds' => [
            'type' => 'java',
            'host' => 'http://10.9.47.164:8026',
        ],
        'verifier' => [
            'type' => 'java',
            'host' => 'http://verifier.youzan.com:8090',
            'sub' => [
                [
                    'mod' => 'verifier.verifier',
                    'host' => 'http://verifier.youzan.com:8090',
                    'type' => 'java'
                ]
            ]
        ],
        'ticket' => [
            'type' => 'yar',
            'host' => 'http://matrix.api.youzan.com/',
        ],
        'courier' => [
            'type' => 'java',
            'host' => 'http://192.168.66.204:8011', //预发 10.10.72.5
            'sub' => [
                [
                    'mod' => 'courier.assignable.admins',
                    'host' => 'http://courier-api.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'courierold' => [
            'type' => 'java',
            'host' => 'http://192.168.66.204:8019'//预发 10.10.72.5
        ],
        'trade' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com',
            'sub' => [
                [
                    'mod' => 'trade.buy.bookByParams',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierBook',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.updateAddressByParams',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierUpdateBuyWay',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.confirmByParams',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.afterPayService.processNormalOrderPaySuccess',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeBook',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateBuyWay',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateAddress',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.tm.query',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStockService.decrease',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStateService.modifyWaitToSend',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.create',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.confirm',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.getCode',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.updateSelfFetchAddress',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.queryGiftInvite',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.updateGiftInvite',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.pickUpGift',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.updateFetchRecord',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.listFetchRecords',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.queryFetchRecordById',
                    'host' => 'http://192.168.66.206:8025',
                    'type' => 'java'
                ],
            ]
        ],
        'tradetask' => [
            'type' => 'local',
            'host' => 'http://trade.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'tradetask.message.send',
                    'host' => 'http://127.0.0.1:8026',
                    'type' => 'java'
                ],
            ]
        ],
        'cart' => [
            'type' => 'local',
            'host' => 'http://trade.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'cart.cartService.addGoods',
                    'host' => 'http://192.168.66.206:8026',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.countGoods',
                    'host' => 'http://192.168.66.206:8026',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.listGoods',
                    'host' => 'http://192.168.66.206:8026',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.listGoodsByPk',
                    'host' => 'http://192.168.66.206:8026',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.deleteGoods',
                    'host' => 'http://192.168.66.206:8026',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.updateGoodsNum',
                    'host' => 'http://192.168.66.206:8026',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.merge',
                    'host' => 'http://192.168.66.206:8026',
                    'type' => 'java'
                ],
            ]
        ],
        'timeout' => [
            'type' => 'local',
            'host' => 'http://trade.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'timeout.order.delivered.delay.increase',
                    'host' => 'http://127.0.0.1:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'timeout.order.delivered.delay.getEndTime',
                    'host' => 'http://127.0.0.1:8025',
                    'type' => 'java'
                ]
            ]
        ],
        'ump' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'goods' => [
            'type' => 'local',
            'host' => 'http://goods.api.youzan.com/'
        ],
        'showcase' => [
            'type' => 'local',
            'host' => 'http://showcase.api.youzan.com/'
        ],
        'fenxiao' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'sinaweibo' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'showcase' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'pay' => [
            'type' => 'local',
            'host' => 'http://pay.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'pay.api.yzpay',
                    'host' => 'http://172.16.4.102:8038',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.payment.checkstand',
                    //'host' => 'http://192.168.66.238:8048',
                    'host' => 'http://172.17.8.254:8048',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.payment.recharge',
                    'host' => 'http://localhost:8128',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.ticket',
                    'host' => 'http://192.168.66.238:8088',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.settlement',
                    'host' => 'http://192.168.66.238:8138',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.yzcoin',
                    'host' => 'http://192.168.66.238:28301',
                    'type' => 'java'
                ],
            ]
        ],
        'funds' => [
            'type' => 'local',
            'host' => 'http://pay.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'funds.wait_settled',
                    'host' => 'http://10.200.175.232:5061/online',
                    'type' => 'java'
                ]
            ]
        ],
        'notice' => [
            'type' => 'local',
            'host' => 'http://notice.api.koudaitong.com/'
        ],
        'fans' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'cp' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'fuwu' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'datacenter' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'common' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'weixin' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'pinjian' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'apps' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'activity' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'open' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],

        'ic' => [
            'type' => 'java',
            'host' => 'http://qabb-test-ic0.h.qima-inc.com',
        ],
        'mock' => [
            'type' => 'yar',
            'host' => 'http://mock.api.koudaitong.com/'
        ],
        'delivery' => [
            'type' => 'java',
            'host' => 'http://10.9.27.113:7001'
        ],
        'refund_soa' => [
            'type' => 'java',
            'host' => 'http://10.9.71.125:7001'
        ],

        'scrm' => [
            'type' => 'java',
            'host' => 'http://127.0.0.1:8001',
        ],
        'novaproxy' => [
            'type' => 'http',
            'host' => 'http://dev.novaproxy.s.qima-inc.com:8001',
        ],
        'crm' => [
            'type' => 'yar',
            'host' => 'http://dzt-api.qima-inc.com/',
        ],
    ],

    'qatest' => [
        'account' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com',
            'sub' => [
                [
                    'mod' => 'account.admin',
                    'host' => 'http://10.9.20.165:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.admin.createBbsAccount',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.saveRegistCode',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getRegistCodeInfo',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getListWithTeam',// WAIT: INNER CALL
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.checkLogin',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.checkMobileCode',// WAIT: INNER CALL
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.updateBbsPassword',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.updateBbsNickName',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getBbsUidByAdminId',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getAdminIdByBbsUid',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.platformUser',
                    'host' => 'http://10.9.20.165:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userRead',
                    'host' => 'http://10.9.20.165:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userWrite',
                    'host' => 'http://10.9.20.165:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserRead',
                    'host' => 'http://10.9.20.165:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserWrite',
                    'host' => 'http://10.9.20.165:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagRead',
                    'host' => 'http://10.9.20.165:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagWrite',
                    'host' => 'http://10.9.20.165:8025',
                    'type' => 'java'
                ],

            ]

        ],
        'feeds' => [
            'type' => 'java',
            'host' => 'http://10.9.47.164:8026',
        ],
        'verifier' => [
            'type' => 'java',
            'host' => 'http://verifier.youzan.com:8090',
            'sub' => [
                [
                    'mod' => 'verifier.verifier',
                    'host' => 'http://verifier.youzan.com:8090',
                    'type' => 'java'
                ]
            ]
        ],
        'courier' => [
            'type' => 'java',
            'host' => 'http://10.9.35.225:8011', //预发 10.10.72.5
            #'host' => 'http://10.9.26.248:8011', //预发 10.10.72.5
            'sub' => [
                [
                    'mod' => 'courier.assignable.admins',
                    'host' => 'http://courier-api.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'courierold' => [
            'type' => 'java',
            'host' => 'http://192.168.66.204:8019'//预发 10.10.72.5
        ],
        'trade' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com',
            'sub' => [
                [
                    'mod' => 'trade.buy.bookByParams',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierBook',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.updateAddressByParams',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierUpdateBuyWay',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.confirmByParams',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStockService.decrease',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStateService.modifyWaitToSend',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.afterPayService.processNormalOrderPaySuccess',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeBook',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateBuyWay',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateAddress',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.tm.query',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.updateSelfFetchAddress',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.queryGiftInvite',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.updateGiftInvite',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.pickUpGift',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.updateFetchRecord',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.listFetchRecords',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.queryFetchRecordById',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.create',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.confirm',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.getCode',
                    'host' => 'http://10.9.38.65:8025',
                    'type' => 'java'
                ],
            ]
        ],
        'tradetask' => [
            'type' => 'local',
            'host' => 'http://trade.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'tradetask.message.send',
                    'host' => 'http://10.9.62.147:8025',
                    'type' => 'java'
                ],
            ]
        ],
        'cart' => [
            'type' => 'local',
            'host' => 'http://trade.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'cart.cartService.addGoods',
                    'host' => 'http://10.9.65.122:7001',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.countGoods',
                    'host' => 'http://10.9.65.122:7001',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.listGoods',
                    'host' => 'http://10.9.65.122:7001',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.listGoodsByPk',
                    'host' => 'http://10.9.65.122:7001',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.deleteGoods',
                    'host' => 'http://10.9.65.122:7001',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.updateGoodsNum',
                    'host' => 'http://10.9.65.122:7001',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.merge',
                    'host' => 'http://10.9.65.122:7001',
                    'type' => 'java'
                ],
            ]
        ],
        'timeout' => [
            'type' => 'local',
            'host' => 'http://trade.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'timeout.order.delivered.delay.increase',
                    'host' => 'http://127.0.0.1:8025',
                    'type' => 'java'
                ],
                [
                    'mod' => 'timeout.order.delivered.delay.getEndTime',
                    'host' => 'http://127.0.0.1:8025',
                    'type' => 'java'
                ]
            ]
        ],
        'ump' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'goods' => [
            'type' => 'local',
            'host' => 'http://goods.api.youzan.com/'
        ],
        'showcase' => [
            'type' => 'local',
            'host' => 'http://showcase.api.youzan.com/'
        ],
        'fenxiao' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'sinaweibo' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'showcase' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'pay' => [
            'type' => 'local',
            'host' => 'http://pay.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'pay.api.yzpay',
                    'host' => 'http://10.9.59.58:8038',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.payment.checkstand',
                    //'host' => 'http://192.168.66.238:8048',
                    'host' => 'http://10.9.18.213:8048',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.ticket',
                    'host' => 'http://10.9.59.58:8088',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.settlement',
                    'host' => 'http://10.9.18.207:8088',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.yzcoin',
                    'host' => 'http://10.9.36.29:28203',
                    'type' => 'java'
                ]
            ]
        ],
        'funds' => [
            'type' => 'local',
            'host' => 'http://pay.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'funds.wait_settled',
                    'host' => 'http://10.200.175.232:5061/online',
                    'type' => 'java'
                ]
            ]
        ],
        'notice' => [
            'type' => 'local',
            'host' => 'http://notice.api.koudaitong.com/'
        ],
        'fans' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'cp' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'fuwu' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'datacenter' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'common' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'weixin' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'pinjian' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'apps' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'activity' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'open' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'ic' => [
            'type' => 'java',
            'host' => 'http://qabb-qab-ic0.h.qima-inc.com',
        ],
        'delivery' => [
            'type' => 'java',
            'host' => 'http://10.9.27.149:7001'
        ],
        'ticket' => [
            'type' => 'yar',
            'host' => 'http://matrix.api.youzan.com/',
        ],
        'refund_soa' => [
            'type' => 'java',
            'host' => 'http://10.9.71.125:7001',
        ],
        'scrm' => [
            'type' => 'java',
            'host' => 'http://qabb-qa-novaproxy0:8000',
        ],
        'novaproxy' => [
            'type' => 'http',
            'host' => 'http://qabb-qa-novaproxy0:8000',
        ],
        'crm' => [
            'type' => 'yar',
            'host' => 'http://dzt-api.qima-inc.com/',
        ],
    ],

    'online' => [
        'account' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com:80',
            'sub' => [
                [
                    'mod' => 'account.admin',
                    'host' => 'http://usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUser',
                    'host' => 'http://usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userRead',
                    'host' => 'http://usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userWrite',
                    'host' => 'http://usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserRead',
                    'host' => 'http://usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.platformUserWrite',
                    'host' => 'http://usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagRead',
                    'host' => 'http://usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.userTagWrite',
                    'host' => 'http://usercenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'account.admin.getPersonalInfo',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.createBbsAccount',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.saveRegistCode',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getRegistCodeInfo',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getListWithTeam',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.checkLogin',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.checkMobileCode',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getTeamListByAccount',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.updateBbsPassword',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.updateBbsNickName',
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.updateBbsAccount',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getBbsUidByAdminId',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
                [
                    'mod' => 'account.admin.getAdminIdByBbsUid',// ABORT
                    'host' => '',
                    'type' => 'local'
                ],
            ]
        ],
        'feeds' => [
            'type' => 'java',
            'host' => 'http://10.10.180.144:8025',
        ],
        'verifier' => [
            'type' => 'local',
            'host' => 'http://10.200.175.232:8012',
            'sub' => [
                [
                    'mod' => 'verifier.verifier',
                    'host' => 'http://10.200.175.232:8012',
                    'type' => 'local'
                ]
            ]
        ],
        'courier' => [
            'type' => 'java',
            // 'host' > 'http://10.10.72.5:8011'//预发 10.10.72.5
            // 'host' => 'http://10.10.127.76:8011'//online bc-msg3/4/5 //todo remove
            'host' => 'http://10.200.175.192:8011', //online bc-msg3/4/5
            'sub' => [
                [
                    'mod' => 'courier.assignable.admins',
                    'host' => 'http://courier-api.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'courierold' => [
            'type' => 'java',
            //                      'host' => 'http://10.200.175.232:8011'//online bc-jvapp2/3
            'host' => 'http://10.200.175.232:8019'//online bc-jvapp2/3 兼容的
        ],
        'trade' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com',
            'sub' => [
                [
                    'mod' => 'trade.buy.bookByParams',
                    // 'host' => 'http://10.200.175.193:8011',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierBook',
                    // 'host' => 'http://10.200.175.193:8011',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.updateAddressByParams',
                    // 'host' => 'http://10.200.175.193:8011',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.cashierUpdateBuyWay',
                    // 'host' => 'http://10.200.175.193:8011',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.confirmByParams',
                    // 'host' => 'http://10.200.175.193:8011',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStockService.decrease',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.orderStateService.modifyWaitToSend',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.afterPayService.processNormalOrderPaySuccess',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeBook',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateBuyWay',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.mergeUpdateAddress',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.create',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.confirm',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.selffetch.getCode',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.buy.updateSelfFetchAddress',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.queryGiftInvite',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.updateGiftInvite',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.pickUpGift',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.updateFetchRecord',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.listFetchRecords',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'trade.gift.queryFetchRecordById',
                    'host' => 'http://trade-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'tradetask' => [
            'type' => 'local',
            'host' => 'http://trade.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'tradetask.message.send',
                    'host' => 'http://trade-message-soa.s.qima-inc.com ',
                    'type' => 'java'
                ],
            ]
        ],
        'cart' => [
            'type' => 'local',
            'host' => 'http://trade.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'cart.cartService.addGoods',
                    'host' => 'http://trade-cart-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.countGoods',
                    'host' => 'http://trade-cart-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.listGoods',
                    'host' => 'http://trade-cart-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.listGoodsByPk',
                    'host' => 'http://trade-cart-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.deleteGoods',
                    'host' => 'http://trade-cart-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.updateGoodsNum',
                    'host' => 'http://trade-cart-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'cart.cartService.merge',
                    'host' => 'http://trade-cart-soa.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'timeout' => [
            'type' => 'local',
            'host' => 'http://trade.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'timeout.order.delivered.delay.increase',
                    // 'host' => 'http://10.10.12.64:8025',
                    'host' => 'http://timeoutcenter.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'timeout.order.delivered.delay.getEndTime',
                    'host' => 'http://timeoutcenter.s.qima-inc.com',
                    // 'host' => 'http://10.10.12.64:8025',
                    'type' => 'java'
                ]
            ]
        ],
        'ump' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'goods' => [
            'type' => 'local',
            'host' => 'http://goods.api.youzan.com/'
        ],
        'showcase' => [
            'type' => 'local',
            'host' => 'http://showcase.api.youzan.com/'
        ],
        'fenxiao' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'sinaweibo' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'showcase' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'pay' => [
            'type' => 'local',
            'host' => 'http://pay.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'pay.api',
                    //'host' => 'http://10.10.92.137:8038', //tmp
                    'host' => 'http://10.200.175.96:8038',
                    //'host'  => 'http://10.10.196.122:8038',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.payment',
                    //'host'  => 'http://10.10.92.137:8048', //tmp
                    'host' => 'http://10.200.175.96',
                    //'host'  => 'http://10.10.196.122:8048',
                    'type' => 'java',
                    'domain' => 'pay-payment.s.qima-inc.com',
                ],
                [
                    'mod' => 'pay.payment.recharge',
                    'host' => 'http://pay-payment-recharge.s.qima-inc.com',
                    'type' => 'java',
                ],
                [
                    'mod' => 'pay.ticket',
                    'host' => 'http://pay-ticket.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.settlement',
                    'host' => 'http://settlement.s.qima-inc.com',
                    'type' => 'java'
                ],
                [
                    'mod' => 'pay.yzcoin',
                    'host' => 'http://pay-yzcoin.s.qima-inc.com',
                    'type' => 'java'
                ],
            ]
        ],
        'funds' => [
            'type' => 'local',
            'host' => 'http://pay.api.youzan.com/',
            'sub' => [
                [
                    'mod' => 'funds.wait_settled',
                    'host' => 'http://10.200.175.232:5061/online',
                    'type' => 'java'
                ]
            ]
        ],
        'notice' => [
            'type' => 'local',
            'host' => 'http://notice.api.koudaitong.com/'
        ],
        'fans' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'cp' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'fuwu' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'datacenter' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'common' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'weixin' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'pinjian' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'apps' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'activity' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'open' => [
            'type' => 'local',
            'host' => 'http://api.koudaitong.com/'
        ],
        'ic' => [
            'type' => 'java',
            'host' => 'http://ic.s.qima-inc.com',
        ],
        'delivery' => [
            'type' => 'java',
            'host' => 'http://delivery.s.qima-inc.com'
        ],
        'ticket' => [
            'type' => 'yar',
            'host' => 'http://matrix.api.youzan.com/',
        ],
        'refund_soa' => [
            'type' => 'java',
            'host' => 'http://trade-refund-soa.s.qima-inc.com',
        ],
        'scrm' => [
            'type' => 'http',
            'host' => 'http://novaproxy.s.qima-inc.com'
        ],
        'novaproxy' => [
            'type' => 'http',
            'host' => 'http://novaproxy.s.qima-inc.com'
        ],
        'crm' => [
            'type' => 'yar',
            'host' => 'http://dzt-api.qima-inc.com/',
        ],
    ]
];
