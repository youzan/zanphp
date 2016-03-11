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

        $msg  = '<h3>An uncaught Exception was encountered</h3><p>';
        $msg .= 'Type :&nbsp       <font color=red>' . get_class($e)    . "</font><p>";
        $msg .= 'Message :&nbsp    <font color=red>' . $e->getMessage() . "</font><p>";
        $msg .= 'Filename :&nbsp   <font color=red>' . $e->getFile()    . "</font><p>";
        $msg .= 'Line Number :&nbsp<font color=red>' . $e->getLine()    . "</font><p>";
        $msg .= 'STACK TRACES:&nbsp<font color=red>' . $stackTraces     . "</font><p>";

        return $msg;
    }

}