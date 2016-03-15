<?php
/**
 * @author hupp
 * create date: 16/01/05
 */

namespace Zan\Framework\Network\Http\Request;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class RequestBuilder {

    /**
     * @var Request
     */
    private $request;

    public function __construct(\swoole_http_request $request)
    {
        if (!$request) {
            throw new InvalidArgumentException('invalid request');
        }
        $this->request = new Request($request);
    }

    public function build()
    {
        return $this->request;
    }
}
