<?php

namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Utilities\Types\Dir;
use Zan\Framework\Foundation\Coroutine\Event;
use Zan\Framework\Foundation\Core\Config;

class Tpl
{
    private $_data = [];
    private $_tplPath = '';
    private $_event = '';
    private $_rootPath = '';

    public function __construct(Event $event)
    {
        $that = $this;
        $this->_event = $event;
        $this->_rootPath = Application::getInstance()->getBasePath();
        $this->_event->bind('set_view_vars', function($args) use ($that) {
            $this->setViewVars($args);
        });
    }

    public function load($tpl, array $data = [])
    {
        $path = $this->getTplFullPath($tpl);
        extract(array_merge($this->_data, $data));
        require $path;
    }

    public function setTplPath($dir)
    {
        if(!is_dir($dir)){
            throw new InvalidArgumentException('Invalid tplPath for Layout');
        }
        $dir = Dir::formatPath($dir);
        $this->_tplPath = $dir;
    }

    public function setViewVars(array $data)
    {
        $this->_data = array_merge($this->_data, $data);
    }

    public function getTplFullPath($path)
    {
        if(false !== strpos($path, '.html')) {
            return $path;
        }
        $pathArr = $this->_parsePath($path);
        $pathArr = array_map([$this, '_pathUcfirst'], $pathArr);
        $module = array_shift($pathArr);
        $srcPath = $this->_rootPath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $customViewConfig = Config::get('custom_view_config') ? Config::get('custom_view_config') . DIRECTORY_SEPARATOR : '';
        $fullPath = $srcPath . $customViewConfig .
                $module . DIRECTORY_SEPARATOR .
                'View' . DIRECTORY_SEPARATOR .
                join(DIRECTORY_SEPARATOR, $pathArr) .
                '.html';
        return $fullPath;
    }

    private function _parsePath($path)
    {
        return explode('/', $path);
    }

    private function _pathUcfirst($path)
    {
        return ucfirst($path);
    }

}