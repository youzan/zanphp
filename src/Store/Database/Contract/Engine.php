<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 15:26
 */

namespace Zan\Framework\Store\Database\Contract;

use Zan\Framework\Network\Contract\Connection;

interface Engine {
    public function __construct(Connection $conn);
    public function query($sql, array $config=null);
    public function beginTransaction($autoHandleException=false);
    public function commit();
    public function rollback();
    public function close();
}