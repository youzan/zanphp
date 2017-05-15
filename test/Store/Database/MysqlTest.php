<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */
namespace Zan\Framework\Test\Store\Database;

use Zan\Framework\Store\Database\Flow;
use Zan\Framework\Testing\TaskTest;
use Zan\Framework\Store\Facade\Db;

class MysqlTest extends TaskTest
{
    const INSERT_COUNT = 30;

    public function taskCRUD()
    {
        yield $this->create();
        yield $this->insert();
        yield $this->select();
        yield $this->update();
        yield $this->delete();
    }

    private function create()
    {
        $table = "market_category";
        $flow = new Flow();

        $sql = "DROP TABLE IF EXISTS $table";
        yield $flow->queryRaw($table, $sql);
        $flow = new Flow();
        $sql = "CREATE TABLE $table (
          relation_id int(10) unsigned NOT NULL AUTO_INCREMENT,
          market_id int(10) NOT NULL,
          goods_id int(10) NOT NULL,
          PRIMARY KEY (relation_id)
        ) ENGINE=InnoDB";
        yield $flow->queryRaw($table, $sql);
    }

    private function insert()
    {
        for ($i = 0; $i < static::INSERT_COUNT; $i++) {
            $sid = 'market.category.insert';
            $data = [
                'insert'=> [
                    'market_id' => 1111,
                    'goods_id' => 2222,
                ],
            ];
            $last_insert_id = (yield Db::execute($sid, $data));
            $this->assertTrue($last_insert_id === $i + 1, "Db insert one column failed");
        }

        for ($i = 0; $i < static::INSERT_COUNT; $i++) {
            $sid = 'market.category.insert_multi_rows';
            $data = [
                'inserts' => [
                    [
                        'market_id' => 1111,
                        'goods_id' => 2222,
                    ],
                    [
                        'market_id' => 222,
                        'goods_id' => 333,
                    ],
                ],
            ];
            $last_insert_id = (yield Db::execute($sid, $data));
            $this->assertTrue($last_insert_id === static::INSERT_COUNT + 2 * $i + 1, "Db insert multi column failed");
        }

    }

    public function select()
    {
        $sid = 'market.category.all_rows';
        $data = [];
        $result = (yield Db::execute($sid, $data));
        $this->assertEquals(count($result), 3 * static::INSERT_COUNT, 3 * static::INSERT_COUNT." records excepted");
    }

    public function update()
    {
        $sid = 'market.category.affected_update_by_id';
        $data = [
            'data'=> [
                'market_id' => 12,
            ],
            'var' => [
                'goods_id' => 2222
            ]
        ];
        $result = (yield Db::execute($sid, $data));
        $this->assertTrue($result === 2 * static::INSERT_COUNT, "Db update failed");
    }

    public function delete()
    {
        $sid = 'market.category.delete_all_rows';
        $result = (yield Db::execute($sid, []));
        $this->assertTrue($result, "Db delete failed");
        $sid = 'market.category.all_rows';
        $result = (yield Db::execute($sid, []));
        $this->assertEmpty($result, "Expected database cleared empty");
    }
}