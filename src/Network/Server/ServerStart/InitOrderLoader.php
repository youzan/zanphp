<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/2/26
 * Time: 22:30
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