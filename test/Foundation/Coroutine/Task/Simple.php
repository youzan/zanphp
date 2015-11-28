<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/28
 * Time: 23:16
 */

namespace Zan\Framework\Test\Foundation\Coroutine\Task;


class Simple extends Job {
    public function run() {
        $value = (yield 'simple value');

        $this->context->set('key', $value);

        yield 'simple job done';
    }
}