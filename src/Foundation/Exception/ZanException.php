<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/1
 * Time: 00:18
 */

namespace Zan\Framework\Foundation\Exception;


class ZanException extends \Exception {
    /**
     * @var null
     *  * null : do not logging
     *  * LogLevel CONST ...
     */
    public $logLevel = null;

    /**
     * 用于记录异常出现时的上下文信息
     * @var array
     */
    protected $metaData = [];
}