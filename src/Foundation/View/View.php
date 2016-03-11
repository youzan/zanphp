<?php
namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\View\Layout;
use Zan\Framework\Foundation\View\JsLoader;
use Zan\Framework\Foundation\View\CssLoader;

class View
{
    private $_data = [];
    private $_tpl = '';
    private $_jsLoader = null;
    private $_cssLoader = null;


    public function __construct($tpl, array $data)
    {
        $this->_tpl = $tpl;
        $this->_data = $data;
        $this->_jsLoader = new JsLoader();
        $this->_cssLoader = new CssLoader();
    }

    public static function display($tpl, array $data = [])
    {
        $view = new self($tpl, $data);
        return trim($view->render(), " \r\n");
    }

    public function render()
    {
        $layout = new Layout($this->_tpl, $this->_getViewVars());
        return $layout->render();
    }

    private function _getViewVars()
    {
        $vars = [
            'js' => $this->_jsLoader,
            'css' => $this->_cssLoader,
        ];
        return array_merge($vars, $this->_data);
    }
}