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

use Zan\Framework\Foundation\Coroutine\Event;

class BaseLoader
{
    public $blockResQueue = [];
    public $noBlockResQueue = [];
    public $curBlock = '';
    public $event = null;

    public function __construct(Event $event)
    {
        $that = $this;
        $this->event = $event;
        $this->event->bind('start_block', function($args) use($that) {
            $that->setCurrentBlock($args);
        });
        $this->event->bind('end_block', function ($args) use($that) {
            $this->setCurrentEndBlock($args);
        });
    }

    public function setCurrentBlock($blockName)
    {
        if(isset($this->blockResQueue[$blockName])) {
            $this->blockResQueue[$blockName] = [];
        }
        $this->curBlock = $blockName;
    }

    public function setCurrentEndBlock($blockName)
    {
        if($blockName === $this->curBlock) {
            $this->curBlock = '';
        }
    }

    public function getCdnType()
    {
        $cdnMap = Config::get('cdn_whitelist');
        $pageKey = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $this->query_path;
        if (isset($cdnMap[$pageKey])) {
            return 'new_cdn_static';
        } else {
            return 'up_cdn_static';
        }
    }
}