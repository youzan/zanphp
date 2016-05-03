<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/12
 * Time: 下午5:55
 */
namespace Zan\Framework\Store\Database;

use Zan\Framework\Store\Database\Mysql\Mysqli;
use Zan\Framework\Store\Database\Sql\SqlMap;
use Zan\Framework\Store\Database\Sql\Table;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Contract\Network\Connection;

class Flow
{
    private $engineMap = [
        'Mysqli' => 'Zan\Framework\Store\Database\Mysql\Mysqli',
    ];

    public function query($sid, $data, $options)
    {
        $sqlMap = SqlMap::getInstance()->getSql($sid, $data, $options);
        $database = Table::getInstance()->getDatabase($sqlMap['table']);
        $connection = (yield $this->getConnection($database));
        $driver = $this->getDriver($connection);
        $dbResult = (yield $driver->query($sqlMap['sql']));
        $resultFormatter = new ResultFormatter($dbResult, $sqlMap['result_type']);
        yield $resultFormatter->format();
    }

    public function commit()
    {
        $connection = (yield $this->getConnection());
        $driver = $this->getDriver($connection);
        yield $driver->commit();
    }

    public function rollback()
    {
        $connection = (yield $this->getConnection());
        $driver = $this->getDriver($connection);
        yield $driver->rollback();
    }

    private function getDriver(Connection $connection)
    {
        $engine = $this->parseEngine($connection->getEngine());
        return new $engine($connection);
    }

    private function parseEngine($engine)
    {
        if (!isset($this->engineMap[$engine])) {
            throw new GetConnectionException('can\'t find database engine : '.$engine);
        }
        return $this->engineMap[$engine];
    }

    private function getConnection($database = '')
    {
        $beginTransaction = (yield getContext('begin_transaction', false));
        if (!$beginTransaction) {
            $connection = (yield ConnectionManager::getInstance()->get($database));
            if (!($connection instanceof Connection)) {
                throw new GetConnectionException('get connection error database:'.$database);
            }
            yield $connection;
            return;
        }

        $conKey = 'connection_object';
        $connection = (yield getContext($conKey, null));
        if (null !== $connection && $connection instanceof Connection) {
            yield $connection;
            return;
        }

        $connection = (yield ConnectionManager::getInstance()->get($database));
        if (!($connection instanceof Connection)) {
            throw new GetConnectionException('get connection error database:'.$database);
        }
        $driver = $this->getDriver($connection);
        yield $driver->beginTransaction();
        yield setContext($conKey, $connection);
        yield $connection;
        return;
    }
}
