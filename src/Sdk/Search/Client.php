<?php

namespace Zan\Framework\Sdk\Search;

use Zan\Framework\Foundation\Contract\Async;
use Elasticsearch\Client as EsClient;

class Client implements Async
{
    private $client;

    private $handle;

    private $params;

    public static function newInstance($nodeInfo)
    {
        return new Client($nodeInfo);
    }

    private function __construct($nodeInfo)
    {
        $this->client = new EsClient($nodeInfo);
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function execute(Callable $callback)
    {
        call_user_func($this->handle, $callback);
    }

    public function search()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function info()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $callback);
        };
        return $this;
    }

    public function ping()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $callback);
        };
        return $this;
    }

    public function get()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action){
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function getSource()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function delete()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function deleteByQuery()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function count()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function percolate()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function exists()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function mlt()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function mget()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function msearch()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function create()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function bulk()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function index()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function suggest()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function explain()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function scroll()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

    public function update()
    {
        $action = __FUNCTION__;
        $this->handle = function($callback) use ($action) {
            call_user_func([$this->client, $action], $this->params, $callback);
        };
        return $this;
    }

}