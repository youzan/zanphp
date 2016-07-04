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
use Zan\Framework\Store\Database\Exception\CanNotGetConnectionException;
use Zan\Framework\Store\Database\Exception\DbCommitFailException;

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
            $connection->release();
            return;
        }
        yield $this->finishTransaction();
        return;
    }

    public function rollback()
    {
        $connection = (yield $this->getConnectionByStack());
        $driver = $this->getDriver($connection);
        yield $driver->rollback();
        yield $this->finishTransaction();
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
            throw new CanNotGetConnectionException('can\'t find database engine : '.$engine);
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
            throw new GetConnectionException('commit or rollback get connection error');
        }
        /**
         * 从stack里取出最后存进去的链接 pop后要放回去 要保留链接
         */
        $connection = $connectionStack->pop();
        $connectionStack->push($connection);
        yield $connection;
    }

    private function finishTransaction()
    {
        $taskId = (yield getTaskId());
        $connectionStack = (yield getContext(sprintf(self::CONNECTION_STACK, $taskId), null));
        if (null == $connectionStack or $connectionStack->isEmpty()) {
            return;
        }
        /**
         *  出栈,丢弃已经成功commit或者rollback的链接
         */
        /** @var Connection $connection */
        $connection = $connectionStack->pop();
        if ($connection === null or !$connection instanceof Connection) {
            return;
        }
        $config = $connection->getConfig();
        /**
         * 重置CONNECTION_CONTEXT中的链接
         */
        if (isset($config['database'])) {
            yield setContext(sprintf(self::CONNECTION_CONTEXT, $taskId, $config['database']), null);
        }
        yield setContext(sprintf(self::CONNECTION_STACK, $taskId), $connectionStack->isEmpty() ? null : $connectionStack);
        /**
         * 这里不方便取database 所以没有重置CONNECTION_CONTEXT里保存的链接 bug
         */
        if (true === $connectionStack->isEmpty()) {
            $taskId = (yield getTaskId());
            yield setContext(sprintf(self::BEGIN_TRANSACTION_FLAG, $taskId), false);
        }
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
