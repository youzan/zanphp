<?php

namespace Zan\Framework\Sdk\Log\Track;

class TrackFilter {
    private static $target = [ 
            ["name" => "login","url" => "login.youzan.com/sso/index/login.json"],
            ["name" => "signup","url" => "koudaitong.com/v2/account/index/account.json"],
            ["name" => "certificate","url" => "koudaitong.com/v2/account/certificate/certification.json"],
            ["name" => "modifypwd","url" => "koudaitong.com/v2/account/personal/password.json"],
            ["name" => "modifygoods","url" => "koudaitong.com/v2/showcase/goods/item.json","method" => "PUT"],
            ["name" => "modifybank","url" => "koudaitong.com/v2/trade/newsettlement/withdrawAccount.json"],
            ["name" => "modifystore","url" => "koudaitong.com/v2/setting/store/team.json","method" => "PUT"],
            ["name" => "modifyteam","url" => "koudaitong.com/v2/setting/team/common.json","method" => "PUT"],
            ["name" => "recharge","url" => "koudaitong.com/v2/pay/recharge/CreateRecharge.json","method" => "POST"],
            ["name" => "withdraw","url" => "koudaitong.com/v2/trade/newsettlement/withdrawApply.json","method" => "POST"],
            ["name" => "createteam","url" => "koudaitong.com/v2/account/team/team.json","method" => "POST"],
            ["name" => "modifyfeature","url" => "koudaitong.com/v2/showcase/feature/item.json","method" => "PUT"],
            ["name" => "deletefeature","url" => "koudaitong.com/v2/showcase/feature/item.json","method" => "DELETE"],
            ["name" => "deletegoods","url" => "koudaitong.com/v2/showcase/goods/item.json","method" => "DELETE"],

            ["name" => "takedowngoods","url" => "koudaitong.com/v2/showcase/goods/takeDown.json","method" => "PUT"],
            ["name" => "takeupgoods","url" => "koudaitong.com/v2/showcase/goods/takeUp.json","method" => "PUT"],

            ["name" => "login","url" => "wap.koudaitong.com/v2/buyer/auth/authlogin.json","method" => "POST"],
            ["name" => "signup","url" => "wap.koudaitong.com/v2/buyer/auth/authRegister.json","method" => "POST"],
            ["name" => "pay","url" => "trade.koudaitong.com/trade/order/pay.json","method" => "POST"],
            ["name" => "address","url" => "trade.koudaitong.com/trade/order/address.json","method" => "POST"],
            ["name" => "book","url" => "trade.koudaitong.com/v2/trade/order/book.json","method" => "POST"],
            ["name" => "reviews","url" => "wap.koudaitong.com/v2/trade/reviews/reviews.json","method" => "POST"],
            
    ];

    public static function start() {
        $host = $GLOBALS['_SERVER']['HTTP_HOST'];
        $uri = $GLOBALS['_SERVER']['REQUEST_URI'];
        $method = $GLOBALS['_SERVER']['REQUEST_METHOD'];
        foreach ( self::$target as $item ) {
            if (strpos($host . $uri . "?", $item['url'] . "?") === 0) { // 加上问号是为了去除jsonp的影响
                if (isset($item['method']) && $method != $item['method']) {
                    continue;
                }
                $ip = self::getIp();
                $ua = $GLOBALS['_SERVER']['HTTP_USER_AGENT'];
                $get = $GLOBALS['_GET'];
                $post = $GLOBALS['_POST'];
                if ($item['name'] == "address" && isset($post['address']['order_no']) && $post['address']['order_no']){
                    break;
                }
                $request = self::buildRequest($item, $post, $get);
                $cookies = $GLOBALS["_COOKIE"];
                $userId = isset($cookies['user_id']) ? $cookies['user_id'] : '';
                $kdtId = isset($cookies['kdt_id']) ? $cookies['kdt_id'] : '';
                $yzUserId = isset($cookies['youzan_user_id']) ? $cookies['youzan_user_id'] : '';
                $extra = [ 
                    'request' => $request,
                    'user_info' => [ 
                        'ip' => $ip,
                        'ua' => $ua,
                        'user_id' => $userId,
                        'youzan_user_id' => $yzUserId,
                        'kdt_id' => $kdtId 
                    ] 
                ];
                $logger = Track::get("user_track", "fengkong", $item['name'], true, "fengkong");
                $logger->info($host . $uri, null, $extra);
                break;
            }
        }
    }

    private static function getIp() {
        $keys = [ 
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR' 
        ];
        foreach ( $keys as $key ) {
            if (isset($GLOBALS['_SERVER'][$key])) {
                return $GLOBALS['_SERVER'][$key];
            }
        }
        return "0.0.0.0";
    }
    
    private static function buildRequest($item, $post, $get){
        if ("modifygoods" == $item['name']) {
            $data = json_decode($post['data'], true)[0];
            $request = [
                    'alias' => $post['alias'],
                    'goods_id' => $post['id'],
                    'origin_goods_id' => isset($data['origin_kdt_id']) ? $data['origin_kdt_id'] : '',
                    'title' => $data['title'],
                    'delivery_template_id' => $data['delivery_template_id'],
                    'postage' => $data['postage'],
                    'quota' => $data['quota'],
                    'delivery' => $data['delivery'],
                    'tag' => $data['tag'],
                    'join_level_discount' => $data['join_level_discount'],
                    'take_down_time' => $data['take_down_time'],
                    'total_stock' => $data['total_stock'],
                    'is_display' => $post['is_display'],
                    'price' => $data['price'],
                    'start_sold_time' => $data['start_sold_time'],
                    'sold_time' => $data['sold_time']
            ];
            if (isset($data['stock'])) {
                $stock = [ ];
                foreach ( $data['stock'] as $s ) {
                    $stock[] = [
                            'id' => isset($s['id']) ? $s['id'] : '',
                            'price' => isset($s['price']) ? $s['price'] : '',
                            'stock_num' => isset($s['stock_num']) ? $s['stock_num'] : ''
                    ];
                }
                $request['stock'] = $stock;
            }
        } else if ("modifyfeature" == $item['name'] || "deletefeature" == $item['name']) {
            $request['id'] = $post['id'];
            if ("deletefeature" == $item['name']) {
                $request['is_delete'] = 1;
            } else {
                $data = json_decode($post['data'], true)[0];
                $request['title'] = $data['title'];
                $request['alias'] = $post['alias'];
                $request['is_display'] = $post['is_display'];
            }
        } else if ("deletegoods" == $item['name']) {
            $request = ['goods_id' => $post['id']];
        } else if ("takedowngoods" == $item['name'] || "takeupgoods" == $item['name']) {
            $request = ['goods_id' => $post['goodsid']];
        } else {
            $request = array_merge($get, $post);
        }
        return $request;
    }
}
