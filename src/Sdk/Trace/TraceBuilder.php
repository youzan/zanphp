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

    protected function lintSeparator(array &$data)
    {
        array_walk($data, function(&$str) {
            $str = str_replace("\t", str_repeat(chr(32), 4), $str);
        });
    }

    public function buildHeader(array $header)
    {
        $this->lintSeparator($header);
        $this->data .= sprintf("%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t\n", ...$header);
    }

    public function buildTransaction(array $transaction)
    {
        $this->lintSeparator($transaction);
        $this->data .= sprintf("%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t\n", ...$transaction);
    }

    public function commitTransaction(array $transaction)
    {
        $this->lintSeparator($transaction);
        $this->data .= sprintf("%s\t%s\t%s\t%s\t%s\t%s\t\n", ...$transaction);
    }

    public function buildEvent(array $event)
    {
        $this->lintSeparator($event);
        $this->data .= sprintf("%s\t%s\t%s\t%s\t%s\t\n", ...$event);
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

        $microtime = str_replace('.', '', microtime(true));

        $data = [
            Application::getInstance()->getName(),
            self::$hexIp,
            $microtime,
            rand(100000, 999999)
        ];
        $data = implode('-', $data);
        return $data;
    }
}