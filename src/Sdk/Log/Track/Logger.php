<?php

namespace Zan\Framework\Sdk\Log\Track;

use Exception;

abstract class Logger {
    protected $app;
    protected $module;
    protected $type;
    protected $topic;

    public function __construct($app, $module, $type, $topic = '') {
        $this->app = $app;
        $this->module = $module;
        $this->type = $type;
        $this->topic = $topic;
        $this->init();
    }

    /**
     *
     * @param string $msg
     *            一段简短的描述日志内容的文字，如 redis connection refused
     * @param Exception $e
     *            如果是个错误日志，传入exception对象
     * @param object $extra
     *            额外的需要保存的信息
     */
    public abstract function debug($msg, Exception $e = null, $extra = null);

    /**
     *
     * @param string $msg
     *            一段简短的描述日志内容的文字，如 redis connection refused
     * @param Exception $e
     *            如果是个错误日志，传入exception对象
     * @param object $extra
     *            额外的需要保存的信息
     */
    public abstract function info($msg, Exception $e = null, $extra = null);

    /**
     *
     * @param string $msg
     *            一段简短的描述日志内容的文字，如 redis connection refused
     * @param Exception $e
     *            如果是个错误日志，传入exception对象
     * @param object $extra
     *            额外的需要保存的信息
     */
    public abstract function warn($msg, Exception $e = null, $extra = null);

    /**
     *
     * @param string $msg
     *            一段简短的描述日志内容的文字，如 redis connection refused
     * @param Exception $e
     *            如果是个错误日志，传入exception对象
     * @param object $extra
     *            额外的需要保存的信息
     */
    public abstract function error($msg, Exception $e = null, $extra = null);

    protected abstract function init();
}
