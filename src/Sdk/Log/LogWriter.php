<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/5/19
 * Time: 17:41
 */

namespace Zan\Framework\Sdk\Log;


interface LogWriter {
    public function write($data);
}