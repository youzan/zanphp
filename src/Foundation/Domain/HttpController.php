<?php

namespace Zan\Framework\Foundation\Domain;

use Zan\Framework\Network\Http\Response\Response;
use Zan\Framework\Network\Http\Response\JsonResponse;
use Zan\Framework\Foundation\View\View;

class HttpController extends Controller
{
    private $_viewData = [];

    public function output($content)
    {
        return new Response($content);
    }

    public function display($tpl)
    {
        $content = View::display($tpl, $this->_viewData);
        return $this->output($content);
    }

    public function assign($key, $value)
    {
        $this->_viewData[$key] = $value;
    }

    public function r($code, $msg, $data)
    {
        $data = [
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data,
        ];
        return new JsonResponse($data);
    }

    protected function dispatch($action,$mode=0)
    {
        switch($mode){
        }
    }
}
