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
    const CONNECTION_CONTEXT = 'connection_context';
    const CONNECTION_STACK = 'connection_stack';

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

    public function beginTransaction()
    {
        yield setContext('begin_transaction', true);
    }

    public function commit()
    {
        $connection = (yield $this->getConnectionByStack());
        $driver = $this->getDriver($connection);
        yield $driver->commit();
        return;
    }

    public function rollback()
    {
        $connection = (yield $this->getConnectionByStack());
        $driver = $this->getDriver($connection);
        yield $driver->rollback();
        return;
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

    private function getConnection($database)
    {
        $beginTransaction = (yield getContext('begin_transaction', false));
        if (!$beginTransaction) {
            yield $this->getConnectionByConnectionManager($database);
            return;
        }

        $connection = (yield getContext(self::CONNECTION_CONTEXT . '_' . $database, null));
        if (null !== $connection && $connection instanceof Connection) {
            yield $connection;
            return;
        }

        $connection = (yield $this->getConnectionByConnectionManager($database));
        yield $this->setTransaction($database, $connection);
        yield $connection;
        return;
    }

    private function getConnectionByStack()
    {
        $connectionStack = (yield getContext(self::CONNECTION_STACK, null));
        if (null == $connectionStack) {
            throw new GetConnectionException('commit or rollback get connection error');
        }
        $connection = $connectionStack->pop();
        yield setContext(self::CONNECTION_STACK, $connectionStack->isEmpty() === true ? null : $connectionStack);
        if (true === $connectionStack->isEmpty()) {
            yield setContext('begin_transaction', false);
        }
        yield $connection;
    }

    private function getConnectionByConnectionManager($database)
    {
        $connection = (yield ConnectionManager::getInstance()->get($database));
        if (!($connection instanceof Connection)) {
            throw new GetConnectionException('get connection error database:'.$database);
        }
        yield $connection;
    }

    private function setTransaction($database, $connection)
    {
        $driver = $this->getDriver($connection);
        yield $driver->beginTransaction();
        yield setContext(self::CONNECTION_CONTEXT . '_' . $database, $connection);
        $connectionStack = (yield getContext(self::CONNECTION_STACK, null));
        if (null !== $connectionStack && $connectionStack instanceof \SplStack) {
            $connectionStack->push($connection);
            yield setContext(self::CONNECTION_STACK, $connectionStack);
            return;
        }
        yield $this->resetConnectionStack($connection);
    }

    private function resetConnectionStack($connection)
    {
        $connectionStack = new \SplStack();
        $connectionStack->push($connection);
        yield setContext(self::CONNECTION_STACK, $connectionStack);
    }
}
