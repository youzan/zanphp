<?php
namespace Zan\Framework\Store\Database;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliTransactionException;
use Zan\Framework\Store\Database\Mysql\Mysql;
use Zan\Framework\Store\Database\Mysql\MysqliResult;
use Zan\Framework\Store\Database\Sql\SqlMap;
use Zan\Framework\Store\Database\Sql\Table;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Store\Database\Exception\CanNotFindDatabaseEngineException;
use Zan\Framework\Store\Database\Exception\DbCommitFailException;
use Zan\Framework\Store\Database\Exception\CanNotGetConnectionByStackException;
use Zan\Framework\Store\Database\Exception\CanNotGetConnectionByConnectionManagerException;
use Zan\Framework\Store\Database\Exception\DbRollbackFailException;

use Zan\Framework\Store\Database\Mysql\Exception\MysqliConnectionLostException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryTimeoutException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliSqlSyntaxException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryDuplicateEntryUniqueKeyException;

use SplStack;
use Zan\Framework\Utilities\Types\ObjectArray;

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

    const CONNECTION_TASKID_STACK = 'connection_taskid_stack';

    const ACTIVE_CONNECTION_CONTEXT_KEY= 'mysql_active_connections';

    private $engineMap = [
        'Mysqli' => Mysql::class,
    ];

    public function query($sid, $data, $options)
    {
        $sqlMap = SqlMap::getInstance()->getSql($sid, $data, $options);
        $sqlLog = Config::get("monitor.sql");
        if (Debug::get() && is_array($sqlLog) && isset($sqlLog['path'])) {
            $dir = dirname($sqlLog['path']);
            if ((!file_exists($sqlLog['path']) && is_writable($dir)) || is_writable($sqlLog['path'])) {
                swoole_async_write($sqlLog['path'], date("Y-m-d H:i:s", time())."  ".$sqlMap['sql']."\n", -1);
            }
        }

        $database = Table::getInstance()->getDatabase($sqlMap['table']);
        $connection = (yield $this->getConnection($database));
        $driver = $this->getDriver($connection);
        try {
            $dbResult = (yield $driver->query($sqlMap['sql']));
        } catch (\Throwable $t) {
            yield $this->queryException($t, $connection);
            throw $t;
        } catch (\Exception $e) {
            yield $this->queryException($e, $connection);
            throw $e;
        }
        if (isset($sqlMap['count_alias'])) {
            $driver->setCountAlias($sqlMap['count_alias']);
        }
        $resultFormatter = new ResultFormatter($dbResult, $sqlMap['result_type']);
        $result = (yield $resultFormatter->format());
        yield $this->releaseConnection($connection);
        yield $result;
    }

    public function queryRaw($table, $sql)
    {
        $sqlLog = Config::get("monitor.sql");
        if (Debug::get() && is_array($sqlLog) && isset($sqlLog['path'])) {
            $dir = dirname($sqlLog['path']);
            if ((!file_exists($sqlLog['path']) && is_writable($dir)) || is_writable($sqlLog['path']))
                file_put_contents($sqlLog['path'], date("Y-m-d H:i:s", time())."  ".$sql."\n", FILE_APPEND);
        }
        $database = Table::getInstance()->getDatabase($table);
        $connection = (yield $this->getConnection($database));
        $driver = $this->getDriver($connection);
        try {
            $dbResult = (yield $driver->query($sql));
        }  catch (\Throwable $t) {
            yield $this->queryException($t, $connection);
            throw $t;
        } catch (\Exception $e) {
            yield $this->queryException($e, $connection);
            throw $e;
        }
        yield $this->releaseConnection($connection);
        yield $dbResult;
    }

    public function beginTransaction($flags = 0)
    {
        $taskId = (yield getTaskId());
        yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), $flags);
    }

    public function commit($flags = 0)
    {
        $connection = (yield $this->getConnectionByStack());
        $driver = $this->getDriver($connection);
        try {
            /* @var MysqliResult $commit */
            $commit = (yield $driver->commit($flags));
        } catch (\Throwable $t) {
            throw new DbCommitFailException($t->getMessage(), $t->getCode());
        } catch (\Exception $e) {
            throw new DbCommitFailException($e->getMessage(), $e->getCode());
        }
        if ((yield $commit->fetchRows()) === true) {
            yield $this->finishTransaction();
            return;
        }
        throw new DbCommitFailException();
    }

    public function rollback($flags = 0)
    {
        $connection = (yield $this->getConnectionByStack());
        $driver = $this->getDriver($connection);
        try {
            /* @var MysqliResult $rollback */
            $rollback = (yield $driver->rollback($flags));
        } catch (\Throwable $t) {
            yield $this->dealRollbackError();
            throw new DbRollbackFailException($t->getMessage(), $t->getCode());
        } catch (\Exception $e) {
            yield $this->dealRollbackError();
            throw new DbRollbackFailException($e->getMessage(), $e->getCode());
        }
        if ((yield $rollback->fetchRows()) === true) {
            yield $this->finishTransaction();
            return;
        }
        yield $this->dealRollbackError();
        throw new DbRollbackFailException();
    }

    /**
     * @param Connection $connection
     * @return Mysql
     */
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
        if ($beginTransaction === false || $beginTransaction === null) {
            yield $this->getConnectionByConnectionManager($database);
            return;
        }
        $flags = $beginTransaction;
        $connection = (yield getContext(sprintf(self::CONNECTION_CONTEXT, $taskId, $database), null));
        if (null !== $connection && $connection instanceof Connection) {
            yield $connection;
            return;
        }

        $connection = (yield $this->getConnectionByConnectionManager($database));
        yield $this->setTransaction($database, $connection, $flags);
        yield $connection;
        return;
    }

    private function getConnectionByStack()
    {
        $taskId = (yield getTaskId());
        $connectionStack = (yield getContext(sprintf(self::CONNECTION_STACK, $taskId), null));
        if (null == $connectionStack || $connectionStack->isEmpty()) {
            throw new CanNotGetConnectionByStackException('commit or rollback get connection error');
        }
        $connection = $connectionStack->pop();
        $connectionStack->push($connection);
        yield $connection;
    }

    private function finishTransaction()
    {
        $taskId = (yield getTaskId());
        $connectionStack = (yield getContext(sprintf(self::CONNECTION_STACK, $taskId), null));
        if (null == $connectionStack || $connectionStack->isEmpty()) {
            yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), false);
            return;
        }

        $connection = $connectionStack->pop();
        yield $this->deleteActiveConnectionFromContext($connection);
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
        if (null == $connectionStack || $connectionStack->isEmpty()) {
            yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), false);
            return;
        }

        $connection = $connectionStack->pop();
        yield $this->deleteActiveConnectionFromContext($connection);
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
        yield $this->insertActiveConnectionIntoContext($connection);
        yield $connection;
    }

    private function insertActiveConnectionIntoContext($connection)
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            $activeConnections = new ObjectArray();
        }
        $activeConnections->push($connection);
        yield setContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, $activeConnections);
    }

    private function deleteActiveConnectionFromContext($connection)
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            return;
        }
        $activeConnections->remove($connection);
    }

    private function closeActiveConnectionFromContext()
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            return;
        }
        while (!$activeConnections->isEmpty()) {
            $connection = $activeConnections->pop();
            if ($connection instanceof Connection) {
                $connection->close();
            }
        }
    }

    private function setTransaction($database, $connection, $flags = 0)
    {
        $driver = $this->getDriver($connection);
        $taskId = (yield getTaskId());
        /* @var MysqliResult $txBegin */
        $txBegin = (yield $driver->beginTransaction($flags));
        if ((yield $txBegin->fetchRows()) !== true) {
            throw new MysqliTransactionException('mysqli begin transaction error');
        }
        yield setContext(sprintf(self::CONNECTION_CONTEXT, $taskId, $database), $connection);
        $connectionStack = (yield getContext(sprintf(self::CONNECTION_STACK, $taskId), null));
        if (null !== $connectionStack && $connectionStack instanceof SplStack) {
            $connectionStack->push($connection);
            yield setContext(sprintf(self::CONNECTION_STACK, $taskId), $connectionStack);
            return;
        }
        yield $this->resetConnectionStack($connection);
        yield $this->pushTaskIdInConnectionTaskIdStack();
    }

    private function resetConnectionStack($connection)
    {
        $taskId = (yield getTaskId());
        $connectionStack = new SplStack();
        $connectionStack->push($connection);
        yield setContext(sprintf(self::CONNECTION_STACK, $taskId), $connectionStack);
    }

    private function pushTaskIdInConnectionTaskIdStack()
    {
        $taskId = (yield getTaskId());
        $taskIdStack = (yield getContext(self::CONNECTION_TASKID_STACK, null));
        if (null !== $taskIdStack && $taskIdStack instanceof SplStack) {
            $taskIdStack->push($taskId);
            return;
        }
        $taskIdStack = new SplStack();
        $taskIdStack->push($taskId);
        yield setContext(self::CONNECTION_TASKID_STACK, $taskIdStack);
    }

    private function releaseConnection($connection)
    {
        $taskId = (yield getTaskId());
        $beginTransaction = (yield getContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), false));
        if ($beginTransaction === false) {
            yield $this->deleteActiveConnectionFromContext($connection);
            $connection->release();
        }
        yield true;
    }

    public function terminate()
    {
        yield $this->closeActiveConnectionFromContext();
        $taskIdStack = (yield getContext(self::CONNECTION_TASKID_STACK, null));
        if (null == $taskIdStack || !($taskIdStack instanceof SplStack)) {
            return;
        }

        while (!$taskIdStack->isEmpty()) {
            $taskId = $taskIdStack->pop();
            $connectionStack = (yield getContext(sprintf(self::CONNECTION_STACK, $taskId), null));
            if (null == $connectionStack || !($connectionStack instanceof SplStack)) {
                continue;
            }
            while (!$connectionStack->isEmpty()) {
                $connection = $connectionStack->pop();
                //close
                yield $this->deleteActiveConnectionFromContext($connection);
                $connection->close();

                $config = $connection->getConfig();
                if (isset($config['pool']['pool_name'])) {
                    yield setContext(sprintf(self::CONNECTION_CONTEXT, $taskId, $config['pool']['pool_name']), null);
                }
            }
            yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), null);
            yield setContext(sprintf(self::CONNECTION_STACK, $taskId), null);
        }
        yield setContext(self::CONNECTION_TASKID_STACK, null);
    }

    private function queryException($exception, $connection)
    {
        if (!($connection instanceof Connection)) {
            return;
        }

        yield $this->deleteActiveConnectionFromContext($connection);
        switch ($exception) {
            case $exception instanceof MysqliConnectionLostException:
                $connection->close();
                break;
            case $exception instanceof MysqliSqlSyntaxException:
                $connection->release();
                break;
            case $exception instanceof MysqliQueryDuplicateEntryUniqueKeyException:
                $connection->release();
                break;
            case $exception instanceof MysqliQueryException:
                $connection->close();
                break;
            case $exception instanceof MysqliQueryTimeoutException:
                $connection->close();
                break;
            default :
                $connection->close();
                break;
        }
    }
}
