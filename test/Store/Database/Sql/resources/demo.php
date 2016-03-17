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
            SELECT test_name as gn FROM test
            WHERE 1
            AND test_id = #{id}
            AND category_id = #{category_id}
            GROUP BY id
            ORDER BY ID DESC
            //#GROUP# #ORDER# #LIMIT#
        ",
    ],
    'demo_sql_id1_1'      => [
        'sql'           => "
            SELECT * FROM test
            WHERE 1
            AND `name` = #{name}
            AND `nick_name` = #{nick_name}
            #LIMIT#
        ",
    ],
    'demo_sql_id2'      => [
        'require'       => [],
        'limit'         => [],
        'sql'           => "
            SELECT * FROM test
            WHERE 1
            #WHERE# #GROUP# #ORDER# #LIMIT#
        ",
    ],

    'demo_sql_update1'  => [
        //where and
        'require'       => [],
        'limit'         => [],
        //TODO ...
//        'dataKeys'      => [
//            'name', 'gender'
//        ],
        'groupByKey'    => [

        ],
        'orderByKeys'   => [
        ],
        'sql'           => "
            UPDATE test
            SET #DATA#
            WHERE 1
            AND `name` = #NAME#
            AND #AND#
            AND #AND1#
        ",
    ],


];