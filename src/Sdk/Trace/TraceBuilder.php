<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/31
 * Time: 下午4:54
 */

namespace Zan\Framework\Sdk\Trace;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Env;

class TraceBuilder
{
    private $data = "";
    private static $hexIp = null;

    public function buildHeader(array $header)
    {
        array_unshift($header, "%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t\n");
        $this->data .= call_user_func_array("sprintf", $header);
    }

    public function buildTransaction(array $transaction)
    {
        array_unshift($transaction, "%s\t%s\t%s\t\n");
        $this->data .= call_user_func_array("sprintf", $transaction);
    }

    public function commitTransaction(array $transaction)
    {
        array_unshift($transaction, "%s\t%s\t%s\t%s\t%s\t%s\t\n");
        $this->data .= call_user_func_array("sprintf", $transaction);
    }

    public function buildEvent(array $event)
    {
        array_unshift($event, "%s\t%s\t%s\t%s\t%s\t\n");
        $this->data .= call_user_func_array("sprintf", $event);
    }

    public function isNotEmpty() {
        return !empty($this->data);
    }

    public function getData()
    {
        $strlen = pack("N*", strlen($this->data));
        return $strlen . $this->data;
    }

    public static function generateId()
    {
        if (null === self::$hexIp) {
            self::$hexIp = dechex(ip2long(Env::get('ip')));
            $zeroLen = strlen(self::$hexIp);
            if ($zeroLen < 8) {
                self::$hexIp = '0' . self::$hexIp;
            }
        }

        $data = [
            Application::getInstance()->getName(),
            self::$hexIp,
            floor(time()/3600),
            rand(100000, 999999)
        ];
        $data = implode('-', $data);
        return $data;
    }
}