<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class Request extends \Zan\Framework\Network\Contract\Request{

    private $request;
    private $queryParams;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getRequestUri()
    {
        $requestUri = $this->request->server['REQUEST_URI'];

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
        return array_merge($this->request->get, $this->queryParams);
    }

}