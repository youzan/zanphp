<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http;

Class Request extends \Zan\Framework\Network\Http\Request\Request{

    private $url;

    public function __construct()
    {

    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        $urlInfo = parse_url($this->url);

        return isset($urlInfo['path']) ? $urlInfo['path'] : '/';
    }

    public function getMethod()
    {
        return 'GET';
    }
}