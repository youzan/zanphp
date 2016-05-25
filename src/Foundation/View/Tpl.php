<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

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