<?php
namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\View\Layout;
use Zan\Framework\Foundation\View\TplLoader;

class View
{
    private $_data = [];
    private $_tpl = '';

    private $_jsLoader = null;
    private $_cssLoader = null;
    private $_tplLoader = null;
    private $_layout = null;

    public function __construct($tpl, array $data = [])
    {
        $this->_tpl = $tpl;
        $this->_data = $data;
        $this->_jsLoader = new JsLoader();
        $this->_cssLoader = new CssLoader();
        $this->_tplLoader = new TplLoader();
    }

    public static function display($tpl, array $data = [])
    {
        $view = new self($tpl, $data);
        return trim($view->render(), " \r\n");
    }

    public function render()
    {
        $this->_layout = new Layout($this->_tplLoader, $this->_tpl);
        $this->_tplLoader->setData($this->_getViewVars());
        return $this->_layout->render();
    }

    private function _getViewVars()
    {
        $loaders = [
            'js' => $this->_jsLoader,
            'css' => $this->_cssLoader,
            'tpl' => $this->_tplLoader,
            'layout' => $this->_layout,
        ];
        return array_merge($loaders, $this->_data);
    }
}