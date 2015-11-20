<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/13
 * Time: 11:38
 */

namespace Zan\Framework\Test\Foundation\Coroutine\Task;


class WaitJob {
    public function run() {
        $data = yield parallel([
            'a' => $this->asyncSeviceCall1(),
            'b' => $this->asyncSeviceCall2(),
        ]);

        $a = $data['a'];
        $b = $data['b'];
    }

    public function asyncSeviceCall1() {
        yield;
    }

    public function asyncSeviceCall2() {
        yield;
    }
}



function parallel($callMaps) {
    yield;
}