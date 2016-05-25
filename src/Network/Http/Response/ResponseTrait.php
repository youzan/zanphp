<?php

namespace Zan\Framework\Network\Http\Response;

use swoole_http_response as SwooleHttpResponse;

trait ResponseTrait
{
    /**
     * Set a header on the Response.
     *
     * @param  string  $key
     * @param  string  $value
     * @param  bool    $replace
     * @return $this
     */
    public function withHeader($key, $value, $replace = true)
    {
        $this->headers->set($key, $value, $replace);

        return $this;
    }

    /**
     * Add an array of headers to the response.
     *
     * @param  array  $headers
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * Add a cookie to the response.
     *
     * @param  \Zan\Framework\Network\Http\Response\Cookie  $cookie
     * @return $this
     */
    public function withCookie($cookie)
    {
        $this->headers->setCookie($cookie);

        return $this;
    }

    /**
     * Add multiple cookies to the response.
     *
     * @param  array  $cookies
     * @return $this
     */
    public function withCookies(array $cookies)
    {
        foreach ($cookies as $cookie) {
            $this->headers->setCookie($cookie);
        }

        return $this;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @param SwooleHttpResponse $swooleHttpResponse
     * @return BaseResponse
     */
    public function sendBy(SwooleHttpResponse $swooleHttpResponse)
    {
        $this->sendHeadersBy($swooleHttpResponse);
        $this->sendContentBy($swooleHttpResponse);
    }

    /**
     * Sends HTTP headers.
     *
     * @param SwooleHttpResponse $swooleHttpResponse
     * @return BaseResponse
     */
    public function sendHeadersBy(SwooleHttpResponse $swooleHttpResponse)
    {
        if (!$this->headers->has('Date')) {
            $this->setDate(\DateTime::createFromFormat('U', time()));
        }

        // headers
        foreach ($this->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                $swooleHttpResponse->header($name, $value);
            }
        }

        // status
        $swooleHttpResponse->status($this->getStatusCode());

        // status
        //header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        // cookies
        foreach ($this->headers->getCookies() as $cookie) {
            $swooleHttpResponse->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }

        return $this;
    }

    /**
     * Sends content for the current web response.
     *
     * @param SwooleHttpResponse $swooleHttpResponse
     * @return BaseResponse
     */
    public function sendContentBy(SwooleHttpResponse $swooleHttpResponse)
    {
        $swooleHttpResponse->end($this->getContent());

        return $this;
    }
}
