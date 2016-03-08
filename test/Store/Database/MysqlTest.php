<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */

namespace Zan\Framework\Test\Network;

use Zan\Framework\Network\Client\ConnectionConfig;

require __DIR__ . '/../../../' . 'src/Test.php';
use Zan\Framework\Store\Database\Mysql\QueryExecuter;
class MysqlTest extends \UnitTest {
    public function testA()
    {

        $a = new QueryExecuter($db);

       $result = $a->query("show tables");

    }
}