<?php
namespace Zan\Framework\Test\Store\NoSQL;

use Zan\Framework\Store\Facade\Cache;
use Zan\Framework\Testing\TaskTest;

/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/29
 * Time: 上午11:47
 */
class RedisTest extends TaskTest {

    public function taskSetGet()
    {
        try {
            $value = "redisTest";
            yield Cache::set("pf.test.test", ["zan", "test"], $value);
            $this->assertEquals($value, "redisTest");
            $result = (yield Cache::get("pf.test.test", ["zan", "test"]));
            $this->assertEquals($result, "redisTest");
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

    }

    public function taskDel()
    {
        try {
            yield Cache::set("pf.test.test", ["zan", "test1"], "redisTest1");
            yield Cache::del("pf.test.test", ["zan", "test1"]);
            $result = (yield Cache::get("pf.test.test", ["zan", "test1"]));
            $this->assertEquals($result, null);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}