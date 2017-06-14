<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/6/12
 * Time: 下午3:16
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Network\Common\TcpClientEx;
use Zan\Framework\Network\Connection\ConnectionManager;


class SystemWriterEx implements LogWriter
{
    private $connKey;

    public function __construct($connKey)
    {
        $this->connKey = $connKey;
    }

    public function write($log)
    {
        try {
            $conn = (yield ConnectionManager::getInstance()->get($this->connKey));
            $tcpClient = new TcpClientEx($conn);
            yield $tcpClient->send($log);
        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $e) {
            echo_exception($e);
        }
    }
}