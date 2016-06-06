<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/31
 * Time: 下午4:54
 */

namespace Zan\Framework\Sdk\Trace;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class TraceBuilder
{
    use Singleton;

    private $data = "";

    public function buildHeader(array $header)
    {
        array_unshift($header, "%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n");
        $this->data .= call_user_func_array("sprintf", $header);
    }

    public function buildTransaction(array $transaction)
    {
        array_unshift($transaction, "%s\t%s\t%s\n");
        $this->data .= call_user_func_array("sprintf", $transaction);
    }

    public function commitTransaction(array $transaction)
    {
        array_unshift($transaction, "%s\t%s\t%s\t%s\t%s\n");
        $this->data .= call_user_func_array("sprintf", $transaction);
    }

    public function buildEvent(array $event)
    {
        array_unshift($event, "%s\t%s\t%s\t%s\t%s\n");
        $this->data .= call_user_func_array("sprintf", $event);
    }

    public function isNotEmpty() {
        return !empty(self::$data);
    }

    public function getData()
    {
        return cat_encode($this->data);
    }

    public static function generateId()
    {
        $data = [
            Application::getInstance()->getName(),
            dechex(ip2long(gethostbyname(gethostname()))),
            substr(time()/rand(0,24), 0, 6),
            rand(100000, 999999)
        ];
        $data = implode('-', $data);
        return $data;
    }
}