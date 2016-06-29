<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/6/8
 * Time: 下午3:39
 */
namespace Zan\Framework\Network\Common;

class Curl
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    private $method;

    private $params;

    private $url;

    private $header = [];

    private $ch;

    private $response;

    /**
     * @var int [millisecond]
     */
    private $timeout = 3000;

    /**
     * @var int [millisecond]
     */
    private $connectTimeout = 0;

    private $errno;

    private $error;

    public function setMethod($method = self::METHOD_GET)
    {
        $this->method = $method;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function setHeader(array $header)
    {
        $this->header = array_merge($this->header, $header);
        return $this;
    }

    public function get($url, $params, $timeout = 3000)
    {
        $this->setMethod(self::METHOD_GET);
        $this->setTimeout($timeout);
        $this->setUrl($url);
        $this->setParams($params);
        return $this->build()->handle()->response();
    }

    public function post($url, $params, $timeout = 3000)
    {
        $this->setMethod(self::METHOD_POST);
        $this->setTimeout($timeout);
        $this->setUrl($url);
        $this->setParams($params);

        return $this->build()->handle()->response();
    }

    public function request($url, $params, $method = self::METHOD_GET, $timeout = 3000)
    {
        //todo
    }

    private function build()
    {
        $this->ch = curl_init();

        if ($this->method == self::METHOD_GET) {
            $this->url = $this->url . '?' . http_build_query($this->params);
        }
        if ($this->method == self::METHOD_POST) {
            $contentType = 'application/json';
            $this->setHeader(
                [
                    'Content-Type' => $contentType
                ]
            );
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($this->params));
        }

        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_ENCODING, "");
        curl_setopt($this->ch, CURLOPT_HEADER, FALSE);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, TRUE);
        return $this;
    }

    private function handle()
    {
        $this->response = curl_exec($this->ch);
        $this->errno = curl_errno($this->ch);
        if ($this->errno) {
            $this->error = curl_error($this->ch);
        }
        return $this;
    }

    private function response()
    {
        return $this->response;
    }

    public function isError()
    {
        return $this->errno ? true : false;
    }

    public function getError()
    {
        return $this->error;
    }
}