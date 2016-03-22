<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/1
 * Time: 23:21
 */

namespace Zan\Framework\Foundation\Console\Output;

use League\CLImate\Util\Writer\WriterInterface;

class Blackhole implements WriterInterface{
    public function write($content)
    {
        return null;
    }
}