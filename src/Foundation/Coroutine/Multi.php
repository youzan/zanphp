<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/1
 * Time: 00:54
 */

namespace Zan\Framework\Foundation\Coroutine;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class Multi {
    use Singleton;

    private $response = [];

    public function add($key, \Generator $coroutine) {

        return $this;
    }

    public function execute() {
        return $this->response;
    }
}