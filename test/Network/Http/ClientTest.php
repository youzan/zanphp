<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Network\Common\Client;
use Zan\Framework\Testing\TaskTest;

class ClientTest extends TaskTest {

    public function setUp()
    {
        $path = __DIR__ . '/config/';
        Path::setConfigPath($path);
        RunMode::set('dev');

        Config::init();
        Config::get('http.client');

    }

    public function taskCall()
    {
//        $option = [
//            'order_no'  => 'E123',
//            'kdt_id'    => 1,
//        ];
//        $result = (yield Client::call('trade.order.detail.byOrderNo', $option));

        $params = [
            'kdt_id' => ['11280',
                '11282',
                '3311',
                '11578',
                '11587',
                '3627',
                '3618',
                '3620',
                '3639',
                '3634',
                '3642',
                '3644',
                '3646',
                '3648',
                '3659',
                '3655',
                '3657',
                '3666',
                '3667',
                '3662',
                '3664',
                '3665',
                '3670',
                '3688',
                '3689',
                '3692',
                '3691',
                '3706',
                '3713',
                '3711',
                '3714',
                '3754',
                '11047',
                '11048',
                '11063',
                '11060',
                '11061',
                '11055',
                '11059',
                '11057',
                '11064',
                '11086',
                '11085',
                '11100',
                '11109',
                '11110',
                '11112',
                '11113',
                '11210',
                '11212',
                '11213',
                '11202',
                '11206',
                '11204',
                '11205',
                '11209',]
        ];

        $result = (yield Client::call('account.team.getTeamByIds',$params));


        //$result = (yield Client::call('account.team.getTeamInfo',['kdt_id'=>13397]));

        var_dump($result);exit;

        $this->assertEquals(3, count($result), 'fail');
    }
}