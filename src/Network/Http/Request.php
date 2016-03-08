<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class Request extends \Zan\Framework\Network\Contract\Request{

    private $request;
    private $queryParams;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getRequestUri()
    {
        $requestUri = $this->request->server['request_uri'];

        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
        }
        if (!$requestUri)
            throw new InvalidArgumentException('Unable to determine the request URI.');

        return $requestUri;
    }

    public function getQueryParams()
    {
        if ($this->queryParams === null) {
            return $this->request->get;
        }
        return array_merge($this->request->get, $this->queryParams);
    }

}