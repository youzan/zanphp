<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class Request extends \Zan\Framework\Network\Contract\Request{

    private $request;
    private $queryParams;

    public function __construct($request)
    {
        $this->request = (new RequestBuilder($request))->build();
    }

    public function getRequestUri()
    {
        $requestUri = $_SERVER['REQUEST_URI'];

        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
        }
        if (!$requestUri)
            throw new InvalidArgument('Unable to determine the request URI.');

        return $requestUri;
    }

    public function getQueryParams()
    {
        if ($this->queryParams === null) {
            return $this->request->get;
        }
        return $this->queryParams;
    }

    public function setGlobalVar()
    {
        $_GET    = isset($this->request->get)    ? isset($this->request->get)    : [];
        $_POST   = isset($this->request->post)   ? isset($this->request->post)   : [];
        $_FILES  = isset($this->request->files)  ? isset($this->request->files)  : [];
        $_COOKIE = isset($this->request->cookie) ? isset($this->request->cookie) : [];
        $_SERVER = isset($this->request->server) ? isset($this->request->server) : [];

        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

        $_SERVER['REQUEST_URI'] = $this->request->server['request_uri'];

        foreach($this->request->header as $key => $value)
        {
            $_key = 'HTTP_'.strtoupper(str_replace('-', '_', $key));
            $_SERVER[$_key] = $value;
        }
        $_SERVER['REMOTE_ADDR'] = $this->request->server['remote_addr'];
    }
}