<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 22:19
 */

return [
    'key' => 'order',
    'name' => '订单',
    'properties' => [
        'orderNo' => [
            'name' => '订单号',
            'dataType' => 'string',
            //default
            //'errorMessage' => '#name#',

            'rules' => [
                //[ matchMethod, matchParameters, errorMsg],
                ['minLength', 5],
                ['maxLength', 40],
                ['regex', '//i'],
            ],
        ],
        'orderType' => [

        ],
    ],
];