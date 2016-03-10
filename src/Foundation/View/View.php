<?php
namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\View\Layout;
use Zan\Framework\Foundation\View\Js;
use Zan\Framework\Foundation\View\Css;
use Zan\Framework\Foundation\View\Tpl;
use Zan\Framework\Foundation\View\Form;

class View
{
    private $_layout = null;
    private $_js = null;
    private $_css = null;
    private $_tpl = null;
    private $_form = null;

    private $_data = [];

    public function __construct($tpl, array $data)
    {
        $this->_layout = new Layout($tpl, $data);
        $this->_js = new Js();
        $this->_css = new Css();
        $this->_tpl = new Tpl();
        $this->_form = new Form();

        $this->_data = $data;
    }

    public static function display($tpl, $data)
    {
        $view = new self($tpl,$data);

        return trim($view->render(), " \r\n");
    }

    public function render()
    {
        extract($this->getViewVars());
    }

    private function getViewVars()
    {
        return [
            'view' => $this,
            'layout' => $this->_layout,
            'form' => $this->_form,
            'js' => $this->_js,
            'css' => $this->_css,
            'tpl' => $this->_tpl
        ];
    }
}