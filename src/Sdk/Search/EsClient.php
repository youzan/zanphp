<?php

namespace Zan\Framework\Sdk\Search;

use Zan\Framework\Foundation\Contract\Async;
use Elasticsearch\Client;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class EsClient implements Async
{
    const DEFAULT_NODE = 'default';

    private $nodeInfo;

    private $client;

    private $handle;

    private $params;

    public static function newInstance($node)
    {
        if (empty($node) || $node === self::DEFAULT_NODE) {
            $node = '';
        } else {
            $node = '.' . $node;
        }
        $nodeInfo = Config::get('connection.elasticsearch' . $node);
        if (!isset($nodeInfo["hosts"][0])) {
            throw new InvalidArgumentException("es node must set one host");
        }

        $self = new EsClient();
        $self->nodeInfo = $nodeInfo;
        return $self;
    }

    private function __construct()
    {

    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    private function parseUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            $url = 'http://' . $url;
        }
        $parts = parse_url($url);
        if ($parts === false) {
            throw new InvalidArgumentException("Could not parse URI");
        }
        if (!isset($parts['port'])) {
            $parts['port'] = 80;
        }
        return [$parts['host'],  $parts['port'],];
    }

    public function execute(Callable $callback, $task)
    {
        $url = $this->nodeInfo["hosts"][0];
        list($host, $port) = $this->parseUrl($url);
        swoole_async_dns_lookup($host, function($host, $ip) use($callback, $port) {
            $this->nodeInfo["hosts"][0] = "$ip:$port";
            $this->client = new Client($this->nodeInfo);
            call_user_func($this->handle, $callback);
        });
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