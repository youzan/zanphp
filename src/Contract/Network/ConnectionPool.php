<?php

namespace Zan\Framework\Contract\Network;

interface ConnectionPool
{

    /**
     * ConnectionPool constructor.
     * @param \Zan\Framework\Contract\Network\ConnectionFactory $connectionFactory
     * @param array $config
     * @param $type
     */
    public function __construct(ConnectionFactory $connectionFactory, array $config, $type);

    /**
     * @param array $config
     * @return bool
     */
    public function reload(array $config);

    /**
     * @return \Zan\Framework\Contract\Network\Connection
     * @TODO 服务器宕机处理???
     */
    public function get();

    /**
     * @param Connection $conn
     * @return bool
     */
    public function remove(Connection $conn);

    /**
     * @param Connection $conn
     * @return bool
     */
    public function recycle(Connection $conn);

}