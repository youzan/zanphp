<?php
/**
 * Created by PhpStorm.
 * User: suqian
 * Date: 16/4/1
 * Time: 下午2:35
 */

return [
    'table'=>'market_category',

    'insert'=>[

        'sql'     => 'INSERT INTO market_category #INSERT#',
    ],

    'batch_insert'=>[
        'require' => [],
        'limit'   => [],
        'sql'     => 'INSERT INTO market_category #INSERTS#',
    ],

    'row_by_name'=>[
        'require' => [],
        'limit'   => [],
        'sql'     => 'SELECT * FROM market_category WHERE market_id=#{market_id}  and parent_id= #{parent_id}  AND category_name= #{category_name}',
    ],

    'row_by_id'=>[
        'require' => [],
        'limit'   => [],
        'sql'     => 'SELECT * FROM market_category WHERE  relation_id= #{relation_id}',
    ],

    'all_rows'=>[
        'require' => [],
        'limit'   => [],
        'sql'     => 'SELECT * FROM market_category'
    ],

    'row_by_categoryid'=>[
        'require' => ['market_id','category_id','parent_id'],
        'limit'   => [],
        'sql'     => 'SELECT * FROM market_category WHERE  market_id= #{market_id} and parent_id= #{parent_id} and category_id=#{category_id}',
    ],

    'select_by_categoryids'=>[
        'require' => ['market_id','category_id','parent_id'],
        'limit'   => [],
        'sql'     => 'SELECT * FROM market_category WHERE  market_id= #{market_id} and parent_id= #{parent_id} and  category_id in #{category_ids}',
    ],

    'select_by_marketid_display'=>[
        'require' => ['market_id'],
        'limit'   => [],
        'sql'     => 'SELECT * FROM market_category WHERE  market_id= #{market_id} and is_display=#{is_display}',
    ],

    'select_by_marketid'=>[
        'require' => ['market_id'],
        'limit'   => [],
        'sql'     => 'SELECT * FROM market_category WHERE  market_id= #{market_id}',
    ],

    'select_by_parentid'=>[
        'require' => ['market_id'],
        'limit'   => [],
        'sql'     => 'SELECT * FROM market_category WHERE  market_id= #{market_id} and parent_id= #{parent_id}',
    ],

    'select_by_parentid_display'=>[
        'require' => ['market_id'],
        'limit'   => [],
        'sql'     => 'SELECT * FROM market_category WHERE  market_id= #{market_id} and parent_id= #{parent_id} and is_display=#{is_display}',
    ],

    'update_by_id'=>[
        'require' => ['relation_id'],
        'limit'   => [],
        'sql'     => ' UPDATE market_category SET #DATA# WHERE relation_id = #{relation_id} ',
    ],
    'update_append_categoryid'=>[
        'require' => ['market_id'],
        'limit'   => [],
        'sql'     => ' UPDATE market_category SET tag_num=tag_num+1 WHERE  market_id= #{market_id} and parent_id= #{parent_id} and category_id=#{category_id}',
    ],
    'delete_by_market_id'=>[
        'require' => ['market_id'],
        'limit'   => [],
        'sql'     => 'DELETE FROM market_category WHERE  market_id= #{market_id}',
    ],
    'delete_all_rows'=>[
        'sql'     => 'DELETE FROM market_category',
    ],
    'count_by_marketid'=>[
        'require' => ['market_id'],
        'limit'   => [],
        'sql'     => 'SELECT #COUNT# FROM market_category WHERE  market_id= #{market_id} and is_display=#{is_display}',
    ],
];