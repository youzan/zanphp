<?php

namespace Zan\Framework\Foundation\Domain;

use Zan\Framework\Network\Http\Response\Response;
use Zan\Framework\Network\Http\Response\JsonResponse;
use Zan\Framework\Foundation\View\View;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Foundation\View\JsVar;

class HttpController extends Controller
{
    protected $viewData = [];
    protected $jsVar = null;

    public function __construct(Request $request, Context $context)
    {
        parent::__construct($request, $context);
        $this->jsVar = new JsVar();
    }

    public function setJsVarBusiness($key, $value)
    {
        $this->jsVar->setBusiness($key, $value);
    }

    public function setShareData($cover, $title, $desc)
    {
        $this->jsVar->setShare('cover', trim($cover));
        $this->jsVar->setShare('title', trim($title));
        $this->jsVar->setShare('desc', trim($desc));
    }

    public function getJsVars()
    {
        return $this->jsVar->get();
    }

    public function output($content)
    {
        return new Response($content);
    }

    public function display($tpl)
    {
        $this->viewData['_js_var'] = $this->getJsVars();
        $content = View::display($tpl, $this->viewData);
        return $this->output($content);
    }

    public function assign($key, $value)
    {
        $this->viewData[$key] = $value;
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
