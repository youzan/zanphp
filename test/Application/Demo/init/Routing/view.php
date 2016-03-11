<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/10
 * Time: 16:40
 */

class view {
    public function __construct() {
        $this->event = new \Zan\Framework\Foundation\Coroutine\Event();
        $this->layout = new Layout();
        $this->js     = new Js();
        $this->tpl    = new Tpl($this);

    }
    public function render($tpl, $data)
    {
        $this->data = $data;
    }

    public function __call($method)
    {
        $methodMap = [
            'block' => $this->layout,
            'endblock' => $this->layout,
            'place' => $this->layout,
            'tpl' => $this->tpl,
            'js'  => $this->js,
        ];

    }

}

class Layout {
    public function extend(){}
    public function block(){}
    public function endBlock(){}
}

class Tpl {
    public function load()
    {

    }
}


$layout->extend('xxx');

$layout->block('xxx');
$js->load('a.js');
$com->load('a');
$layout->endBlock();


$layout->block('xxx');
$js->load('b.js');
$com->load('b');
$layout->endBlock();


$css->load();


$plugin->load('xxx')->rend();


