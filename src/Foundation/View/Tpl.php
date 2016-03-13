<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/6
 * Time: 23:30
 */

namespace Zan\Framework\Foundation\View;

use Zan\Framework\Utilities\Types\Dir;
use Zan\Framework\Foundation\Coroutine\Event;

class Tpl
{
    private $_data = [];
    private $_tplPath = '';
    private $_event = '';

    public function __construct(Event $event)
    {
        $that = $this;
        $this->_event = $event;
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
        if(!preg_match('/^static./i', $path)){
            $path = explode('/', $path);
            $mod = array_shift($path);
            return APP_PATH . $mod . '/views/' . join('/', $path) . '.html';
        }
        $path = substr($path,7);
        $path = COMMON_STATIC_PATH  .  $path . '.html';
        return $path;
    }
}