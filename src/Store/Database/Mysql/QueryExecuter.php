<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Store\Database\Mysql\SqlMap;
use Zan\Framework\Foundation\Core\Event;

class QueryExecuter
{
    /**
     * @var \mysqli
     */
    private $db;

    private $sqlMap;

    public function __construct(\mysqli $db)
    {
        $this->setDb($db);
    }

    private function setDb()
    {
        if (null == $this->db) {
            //todo connectionManage
//            $this->db = new object;
        }
        return $this->db;
    }

    /**
     * @return \mysqli
     */
    public function getDb()
    {
        return $this->db;
    }

    public function execute($sid)
    {

    }

    private function doQuery($sql)
    {
        $mysqli = $this->getDb();
        $result = $mysqli->query($sql, MYSQLI_ASYNC);
        if ($result === false) {
            //todo throw error
        }
        
        yield $this->queryResult($mysqli, $sql);
    }


    private function queryResult()
    {

    }

    private function getSqlMap()
    {
        if (null == $this->sqlMap) {
            $this->sqlMap = $this->createSqlMap();
        }
        return $this->sqlMap;
    }

    private function createSqlMap()
    {
        return new SqlMap();
    }

    /**
     * max connections for mysql client
     * @var int $pool_size
     */
    protected $pool_size;
    /**
     * number of current connection
     * @var int $connection_num
     */
    protected $connection_num;
    /**
     * idle connection
     * @var array $idle_pool
     */
    protected $idle_pool = array();
    /**
     * work connetion
     * @var array $work_pool
     */
    protected $work_pool = array();
    /**
     * database configuration
     * @var array $config
     */
    protected $config = array();
    /**
     * wait connection
     * @var array
     */
    protected $wait_queue = array();
    /**
     * @param array $config
     * @param int $pool_size
     * @throws \Exception
     */
    public function __construct(array $config, $pool_size = 100)
    {
        if (empty($config['host']) ||
            empty($config['database']) ||
            empty($config['user']) ||
            empty($config['password'])
        ) {
            throw new \Exception("require host, database, user, password config.");
        }
        if (!function_exists('swoole_get_mysqli_sock')) {
            throw new \Exception("require swoole_get_mysqli_sock function.");
        }
        if (empty($config['port'])) {
            $config['port'] = 3306;
        }
        $this->config = $config;
        $this->pool_size = $pool_size;
    }
    /**
     * create mysql connection
     */
    protected function createConnection()
    {
        $config = $this->config;
        $db = new \mysqli;
        $db->connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
        if (!empty($config['charset'])) {
            $db->set_charset($config['charset']);
        }
        $db_sock = swoole_get_mysqli_sock($db);
        swoole_event_add($db_sock, array($this, 'onSQLReady'));
        $this->idle_pool[$db_sock] = array(
            'object' => $db,
            'socket' => $db_sock,
        );
        $this->connection_num ++;
    }
    /**
     * remove mysql connection
     * @param $db_sock
     */
    protected function removeConnection($db_sock)
    {
        swoole_event_del($db_sock);
        $this->idle_pool[$db_sock]['object'] -> close();
        unset($this->idle_pool[$db_sock]);
        $this->connection_num --;
    }
    /**
     * @param $db_sock
     * @return bool
     */
    public function onSQLReady($db_sock)
    {
        $task = empty($this->work_pool[$db_sock]) ? null : $this->work_pool[$db_sock];
        if (empty($task)) {
            echo "MySQLi Warning: Maybe SQLReady receive a Close event , such as Mysql server close the socket !\n";
            $this->removeConnection($db_sock);
            return false;
        }
        /**
         * @var \mysqli $mysqli
         */
        $mysqli = $task['mysql']['object'];
        $callback = $task['callback'];
        if ($result = $mysqli->reap_async_query()) {
            call_user_func($callback, $mysqli, $result);
            if (is_object($result)) {
                mysqli_free_result($result);
            }
        } else {
            call_user_func($callback, $mysqli, $result);
            echo "MySQLi Error: " . mysqli_error($mysqli)."\n";
        }
        //release mysqli object
        $this->idle_pool[$task['mysql']['socket']] = $task['mysql'];
        unset($this->work_pool[$db_sock]);
        //fetch a request from wait queue.
        if (count($this->wait_queue) > 0) {
            $idle_n = count($this->idle_pool);
            for ($i = 0; $i < $idle_n; $i++) {
                $new_task = array_shift($this->wait_queue);
                $this->doQuery($new_task['sql'], $new_task['callback']);
            }
        }
    }
    /**
     * @param string $sql
     * @param callable $callback
     */
    public function query($sql, callable $callback)
    {
        //no idle connection
        if (count($this->idle_pool) == 0) {
            if ($this->connection_num < $this->pool_size) {
                $this->createConnection();
                $this->doQuery($sql, $callback);
            } else {
                $this->wait_queue[] = array(
                    'sql'  => $sql,
                    'callback' => $callback,
                );
            }
        } else {
            $this->doQuery($sql, $callback);
        }
    }
    /**
     * @param string $sql
     * @param callable $callback
     */
    protected function doQuery($sql, callable $callback)
    {
        //remove from idle pool
        $db = array_pop($this->idle_pool);
        /**
         * @var \mysqli $mysqli
         */
        $mysqli = $db['object'];
        for ($i = 0; $i < 2; $i++) {
            $result = $mysqli->query($sql, MYSQLI_ASYNC);
            if ($result === false) {
                if ($mysqli->errno == 2013 or $mysqli->errno == 2006) {
                    $mysqli->close();
                    $r = $mysqli->connect();
                    if ($r === true) {
                        continue;
                    }
                } else {
                    echo "server exception. \n";
                    $this->connection_num --;
                    $this->wait_queue[] = array(
                        'sql'  => $sql,
                        'callback' => $callback,
                    );
                }
            }
            break;
        }
        $task['sql'] = $sql;
        $task['callback'] = $callback;
        $task['mysql'] = $db;
        //join to work pool
        $this->work_pool[$db['socket']] = $task;
    }








}