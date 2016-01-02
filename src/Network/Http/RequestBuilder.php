<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class RequestBuilder {

    private $request;

    public function __construct(\swoole_http_request $request) {
        if (!$request) {
            throw new InvalidArgument('invalid request for RequestBuilder');
        }
        $this->request = $request;
    }

    public function build() {
        return $this->request;
    }
}
