<?php
/**
 * @author hupp
 * create date: 16/01/15
 */
namespace Zan\Framework\Network\Http\Request;

use InvalidArgumentException;

class Request extends \Zan\Framework\Network\Contract\Request{

    private $request;
    private $url;
    private $queryParams = [];

    public function __construct(\swoole_http_request $request)
    {
        $this->request = $request;
    }

    public function getUrl()
    {
        if ($this->url) return $this->url;

        $requestUri = $this->request->server['request_uri'];

        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
        }
        if (!$requestUri)
            throw new InvalidArgumentException('Unable to determine the request URI.');

        return $this->url = $requestUri;
    }

    public function get($name, $defaultValue = null)
    {
        $params = $this->getQueryParams();

        return isset($params[$name]) ? $params[$name] : $defaultValue;
    }

    public function getQuery()
    {
        if ($this->request->get) {
            return array_merge($this->request->get, $this->queryParams);
        }
        return $this->queryParams;
    }

    public function getPost()
    {
        if ($this->request->post) {
            return array_merge($this->request->post, $this->queryParams);
        }
        return $this->queryParams;
    }

    public function getQueryParams()
    {
        if ($this->request->get) {
            return array_merge($this->request->get, $this->queryParams);
        }
        if ($this->request->post) {
            return array_merge($this->request->post, $this->queryParams);
        }
        return $this->queryParams;
    }

    public function setQueryParams($params = [])
    {
        $this->queryParams += (array) $params;
    }

    public function getMethod()
    {
        return $this->request->server['request_method'];
    }

    public function getPathInfo()
    {
        return $this->request->server['path_info'];
    }

    public function getHeaders()
    {
        return $this->request->header;
    }

    public function getRequestMethod()
    {
        return $this->request->server['request_method'];
    }

    public function getUserIp()
    {
        return $this->request->server['remote_addr'];
    }

    public function getServerPort()
    {
        return (int) $this->request->server['server_port'];
    }

    public function getQueryString()
    {
        return isset($this->request->server['query_string']) ? $this->request->server['query_string'] : null;
    }

    public function getUserAgent()
    {
        return isset($this->request->header['user-agent']) ? $this->request->header['user-agent'] : null;
    }

    public function getIsAjax()
    {
        return isset($this->request->server['http_x_request_with']) && $this->request->server['http_x_request_with'] === 'XMLHttpRequest';
    }


}