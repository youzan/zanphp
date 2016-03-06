<?php

namespace Zan\Framework\Foundation\Domain;


class HttpController extends Controller {

    public function display()
    {

    }

    public function assign()
    {

    }

    public function r($code, $msg, $data)
    {
    }

    public function output($data)
    {
        $this->respones->setData($data);
        $this->respones->send();
    }

}
