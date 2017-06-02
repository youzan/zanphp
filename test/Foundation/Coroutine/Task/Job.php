<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/28
 * Time: 23:17
 */

namespace Zan\Framework\Test\Foundation\Coroutine\Task;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Test\Foundation\Coroutine\Context;

abstract class Job {
    protected $context = null;

    public function __construct(Context $context) {
        if(!$context) {
            throw new InvalidArgumentException('invlid context for Job __construct');
        }
        $this->context = $context;
    }

    abstract public function run();
}