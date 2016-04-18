<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/4
 * Time: 00:08
 */

namespace Zan\Framework\Contract\Network;


interface Connection
{
    public function getSocket();
    public function release();
    public function close();
    
    public function getEngine();
    public function heartbeat();
}