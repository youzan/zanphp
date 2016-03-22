<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/6
 * Time: 23:30
 */

namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Utilities\Types\Dir;
use Zan\Framework\Foundation\Coroutine\Event;

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
        $fullPath = $this->_rootPath . DIRECTORY_SEPARATOR .
                'src' . DIRECTORY_SEPARATOR .
                $pathArr['module'] . DIRECTORY_SEPARATOR .
                'View' . DIRECTORY_SEPARATOR .
                $pathArr['controller'] . DIRECTORY_SEPARATOR .
                $pathArr['action'] .
                '.html';
        return $fullPath;
    }

    private function _parsePath($path)
    {
        $result = explode('/', $path);
        $pathArr['module'] = isset($result[0]) ? trim($result[0]) : 'index';
        $pathArr['controller'] = isset($result[1]) ? trim($result[1]) : 'index';
        $pathArr['action'] = isset($result[2]) ? trim($result[2]) : 'index';
        return array_map([$this, '_pathUcfirst'], $pathArr);
    }

    private function _pathUcfirst($path)
    {
        return ucfirst($path);
    }

}