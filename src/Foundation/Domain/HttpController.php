<?php

namespace Zan\Framework\Foundation\Domain;


use Zan\Framework\Network\Http\Response\Response;
use Zan\Framework\Network\Http\Response\JsonResponse;

class HttpController extends Controller {
    public function output($content)
    {
        return new Response($content);
    }

    public function display($tpl)
    {

    }

    public function assign()
    {

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
