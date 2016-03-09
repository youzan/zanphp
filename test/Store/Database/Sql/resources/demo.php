<?php
return [
    'table'             => 'test',
    //'validateByModel'   => true,
    'insert'=>[
        'require' => [],
        'limit'   => [],
        'sql'     => 'INSERT INTO test #INSERT#',
    ],

    'demo_sql_id1'      => [
        'sql'           => "
            SELECT goods_name as gn FROM goods
            WHERE 1
            AND goods_id = #{id}
            AND category_id = #{category_id}
            GROUP BY id
            ORDER BY ID DESC
            //#GROUP# #ORDER# #LIMIT#
        ",
    ],
    'demo_sql_id1_1'      => [
        'sql'           => "
            SELECT * FROM goods
            WHERE 1
            AND goods_id = #{id}
            AND category_id = #{category_id}
        ",
    ],
    'demo_sql_id2'      => [
        'require'       => [],
        'limit'         => [],
        'sql'           => "
            SELECT * FROM goods
            WHERE 1
            #WHERE# #ORDER# #GROUP# #LIMIT# #VARS#
        ",
    ],

    'demo_sql_update1'  => [
        //where and
        'require'       => [],
        'limit'         => [],

        //TODO ...
        'dataKeys'      => [
            'goods_name','', ''
        ],
        'groupByKey'    => [

        ],
        'orderByKeys'   => [

        ],
        'sql'           => "
            UPDATE goods
            SET #DATA#
            WHERE 1
            AND goods_id = #{goods.goods_id}
            #AND#
        ",
    ],


];