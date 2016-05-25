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

namespace Zan\Framework\Network\Server\ServerStart;

use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Dir;

class InitOrderLoader implements Bootable{
    private $orderFile = '/.order';

    public function bootstrap($server)
    {
        // TODO: Implement bootstrap() method.
    }
    
    private function load($path)
    {
        if(!$path){
            return [];
        }

        if(!is_dir($path)){
            return [];
        }

        return $this->doLoading($path);
    }

    private function doLoading($path)
    {
        $path = Dir::formatPath($path);

        $sort = $this->loadSortFile($path);
        $files = $this->loadFiles($path);

        return  Arr::sortByArray($files, $sort);
    }

    private function loadFiles($path)
    {
        $files = Dir::glob($path, '*.php', Dir::SCAN_CURRENT_DIR);

        foreach($files as $file){
            require $file;
        }

        $fileNames = Dir::basename($files, '.php');
        return $fileNames;
    }

    private function loadSortFile($path)
    {
        $file = $path . $this->orderFile;
        if(!file_exists($file)){
            return [];
        }

        $data = file_get_contents($file);
        $data = trim($data);
        $data = explode("\n",$data);
        if(!$data){
            return [];
        }

        return $this->parseSortData($data);
    }

    private function parseSortData($data)
    {
        $sort = [];
        foreach($data as $row){
            $row = trim($row);
            if(!$row) continue;

            $sort[] = $row;
        }

        return $sort;
    }

}