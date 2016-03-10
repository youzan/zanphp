<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/1
 * Time: 00:18
 */

namespace Zan\Framework\Foundation\Exception;


class ZanException extends \Exception {

    public function handle(\Exception $e)
    {
        //todo...
    }

    /**
     * In order to facilitate debugging.
     */
    public function getFormatMessage(\Exception $e)
    {
        $find  = ['#', '\n', '\/'];
        $math  = ['<p>#', '<br>', '/'];

        $stackTraces = json_encode($e->getTraceAsString());
        $stackTraces = str_replace($find, $math, $stackTraces);

        $msg  = 'An uncaught Exception was encountered<p>';
        $msg .= 'Type :       <font color=red>' . get_class($e)    . "</font><p>";
        $msg .= 'Message :    <font color=red>' . $e->getMessage() . "</font><p>";
        $msg .= 'Filename :   <font color=red>' . $e->getFile()    . "</font><p>";
        $msg .= 'Line Number :<font color=red>' . $e->getLine()    . "</font><p>";
        $msg .= 'STACK TRACES:<font color=red>' . $stackTraces     . "</font><p>";

        return $msg;
    }
}