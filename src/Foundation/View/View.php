<?php
namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\View\Layout;
use Zan\Framework\Foundation\View\Tpl;
use Zan\Framework\Foundation\View\Js;
use Zan\Framework\Foundation\View\Css;
use Zan\Framework\Foundation\Coroutine\Event;

class View
{
    private $_data = [];
    private $_tplPath = '';

    private $_js = null;
    private $_css = null;
    private $_tpl = null;
    private $_layout = null;

    private $_event = null;

    public function __construct($tplPath, array $data = [])
    {
        $this->_tplPath = $tplPath;
        $this->_data = $data;
        $this->_event = new Event();
        $this->_js = new Js($this->_event);
        $this->_css = new Css($this->_event);
        $this->_tpl = new Tpl($this->_event);
        $this->_layout = new Layout($this->_tpl, $this->_event, $this->_tplPath);
    }

    public static function display($tplPath, array $data = [])
    {
        $view = new self($tplPath, $data);
        return trim($view->render(), " \r\n");
    }

    public function render()
    {
        $this->_tpl->setViewVars($this->_getViewVars());
        return $this->_js->replaceJS($this->_layout->render());
    }

    private function _getViewVars()
    {
        $loaders = [
            'js' => $this->_js,
            'css' => $this->_css,
            'tpl' => $this->_tpl,
            'layout' => $this->_layout,
        ];
        return array_merge($loaders, $this->_data);
    }
}