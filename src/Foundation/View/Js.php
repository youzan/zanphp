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

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\View\BaseLoader;
use Zan\Framework\Utilities\Types\URL;

class Js extends BaseLoader
{
    public function syncLoad($index, $vendor = false, $crossorigin = false)
    {
        $url = $this->getJsUrl($index, $vendor);
        echo "<script src=\"${url}\" onerror=\"_cdnFallback(this)\"";
        if ($crossorigin) {
            echo ' crossorigin="anonymous"';
        }
        echo "></script>";
        return true;
    }

    public function asyncLoad($index, $vendor = false)
    {
        $url = $this->getJsUrl($index, $vendor);
        if ($this->curBlock) {
            $this->blockResQueue[$this->curBlock][] = $url;
        } else {
            $this->noBlockResQueue[] = $url;
        }
        return true;
    }

    public function getJsUrl($index, $vendor = false)
    {
        $isUseCdn = Config::get('js.use_js_cdn');
        $url = $project = '';
        if ($vendor !== false) {
            $url = URL::site($index, $isUseCdn ? $this->getCdnType() : 'static');
        } else {
            $arr = explode('.', $index, 2);
            if ($isUseCdn) {
                $url = URL::site(Config::get($index), $this->getCdnType());
            } else {
                $project = substr($arr[0], 8);
                $url = URL::site($project . '/' . $arr[1] . '/main.js', 'static');
            }
        }
        return $url;
    }

    public function replaceJs($html)
    {
        $asyncJsList = $this->_mergeAsyncJs();
        if (empty($asyncJsList)) return $html;
        $scriptStr = '_js_files=';
        $scriptStr = '<script>' . $scriptStr . json_encode($asyncJsList) . ';</script>';
        $bodyTagLastPos = strrpos($html, '</body>', -1);
        return substr($html, 0, $bodyTagLastPos) . $scriptStr . substr($html, $bodyTagLastPos);
    }

    private function _mergeAsyncJs()
    {
        $blockResQueue = [];
        foreach ($this->blockResQueue as $block => $jsList) {
            $blockResQueue = array_merge($blockResQueue, $jsList);
        }
        return array_values(array_unique(array_merge($blockResQueue, $this->noBlockResQueue)));
    }
}
