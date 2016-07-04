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
use Zan\Framework\Store\Database\Exception\CanNotFindDatabaseEngineException;
use Zan\Framework\Store\Database\Exception\DbCommitFailException;
use Zan\Framework\Store\Database\Exception\CanNotGetConnectionByStackException;
use Zan\Framework\Store\Database\Exception\CanNotGetConnectionByConnectionManagerException;
use Zan\Framework\Store\Database\Exception\DbRollbackFailException;

class Flow
{
    /**
     * 以Task为单位标记是否开启事务
     */
    const BEGIN_TRANSACTION_FLAG = 'begin_transaction_%s';
    /**
     * 在Context里储存开启事务的的链接的Key, 以Task和DatabaseName为单位
     */
    const CONNECTION_CONTEXT = 'connection_context_%s_%s';
    /**
     * 保存以Task为单位的开启事务的连接的栈, (目的是为了commit的时候不用传database name, 针对被调用接口有自己的事务的情况)
     */
    const CONNECTION_STACK = 'connection_stack_%s'; //format with taskId

    private $engineMap = [
        'Mysqli' => Mysqli::class,
    ];

    public function query($sid, $data, $options)
    {
        $sqlMap = SqlMap::getInstance()->getSql($sid, $data, $options);
        $database = Table::getInstance()->getDatabase($sqlMap['table']);
        $connection = (yield $this->getConnection($database));
        $driver = $this->getDriver($connection);
        $dbResult = (yield $driver->query($sqlMap['sql']));
        if (isset($sqlMap['count_alias'])) {
            $driver->setCountAlias($sqlMap['count_alias']);
        }
        $resultFormatter = new ResultFormatter($dbResult, $sqlMap['result_type']);
        yield $resultFormatter->format();
    }

    public function beginTransaction()
    {
        $taskId = (yield getTaskId());
        yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), true);
    }

    public function commit()
    {
        $connection = (yield $this->getConnectionByStack());
        $driver = $this->getDriver($connection);
        try {
            $commit = (yield $driver->commit());
        } catch (\Exception $e) {
            throw new DbCommitFailException();
        }
        if (true === $commit) {
            $this->finishTransaction();
            return;
        }
        throw new DbCommitFailException();
    }

    public function rollback()
    {
        $connection = (yield $this->getConnectionByStack());
        $driver = $this->getDriver($connection);
        try {
            $rollback = (yield $driver->rollback());
        } catch (\Exception $e) {
            $this->dealRollbackError();
            throw new DbRollbackFailException();
        }
        if (true === $rollback) {
            $this->finishTransaction();
            return;
        }
        $this->dealRollbackError();
        throw new DbRollbackFailException();
    }

    private function getDriver(Connection $connection)
    {
        $engine = $this->parseEngine($connection->getEngine());
        return new $engine($connection);
    }

    private function parseEngine($engine)
    {
        if (!isset($this->engineMap[$engine])) {
            throw new CanNotFindDatabaseEngineException('can\'t find database engine : '.$engine);
        }
        return $this->engineMap[$engine];
    }

    private function getConnection($database)
    {
        $taskId = (yield getTaskId());
        $beginTransaction = (yield getContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), false));
        if (!$beginTransaction) {
            yield $this->getConnectionByConnectionManager($database);
            return;
        }
        $connection = (yield getContext(sprintf(self::CONNECTION_CONTEXT, $taskId, $database), null));
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
        $taskId = (yield getTaskId());
        $connectionStack = (yield getContext(sprintf(self::CONNECTION_STACK, $taskId), null));
        if (null == $connectionStack or $connectionStack->isEmpty()) {
            throw new CanNotGetConnectionByStackException('commit or rollback get connection error');
        }
        $connection = $connectionStack->pop();
        $connectionStack->add($connectionStack->count(), $connection);
        yield $connection;
    }

    private function finishTransaction()
    {
        $taskId = (yield getTaskId());
        $connectionStack = (yield getContext(sprintf(self::CONNECTION_STACK, $taskId), null));
        if (null == $connectionStack or $connectionStack->isEmpty()) {
            yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), false);
            return;
        }

        $connection = $connectionStack->pop();
        $connection->release();
        
        if ($connectionStack->isEmpty()) {
            yield setContext(sprintf(self::CONNECTION_STACK, $taskId), null);
            yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), false);
        }
        $config = $connection->getConfig();
        if (isset($config['pool']['pool_name'])) {
            yield setContext(sprintf(self::CONNECTION_CONTEXT, $taskId, $config['pool']['pool_name']), null);
        }
    }

    private function dealRollbackError()
    {
        $taskId = (yield getTaskId());
        $connectionStack = (yield getContext(sprintf(self::CONNECTION_STACK, $taskId), null));
        if (null == $connectionStack or $connectionStack->isEmpty()) {
            yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), false);
            return;
        }

        $connection = $connectionStack->pop();
        $connection->close();

        if ($connectionStack->isEmpty()) {
            yield setContext(sprintf(self::CONNECTION_STACK, $taskId), null);
            yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), false);
        }
        $config = $connection->getConfig();
        if (isset($config['pool']['pool_name'])) {
            yield setContext(sprintf(self::CONNECTION_CONTEXT, $taskId, $config['pool']['pool_name']), null);
        }
    }

    private function getConnectionByConnectionManager($database)
    {
        $connection = (yield ConnectionManager::getInstance()->get($database));
        if (!($connection instanceof Connection)) {
            throw new CanNotGetConnectionByConnectionManagerException('get connection error database:'.$database);
        }
        yield $connection;
    }

    private function setTransaction($database, $connection)
    {
        $driver = $this->getDriver($connection);
        $taskId = (yield getTaskId());
        yield $driver->beginTransaction();
        yield setContext(sprintf(self::CONNECTION_CONTEXT, $taskId, $database), $connection);
        $connectionStack = (yield getContext(sprintf(self::CONNECTION_STACK, $taskId), null));
        if (null !== $connectionStack && $connectionStack instanceof \SplStack) {
            $connectionStack->push($connection);
            yield setContext(sprintf(self::CONNECTION_STACK, $taskId), $connectionStack);
            return;
        }
        yield $this->resetConnectionStack($connection);
    }

    private function resetConnectionStack($connection)
    {
        $taskId = (yield getTaskId());
        $connectionStack = new \SplStack();
        $connectionStack->push($connection);
        yield setContext(sprintf(self::CONNECTION_STACK, $taskId), $connectionStack);
    }
}
