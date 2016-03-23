<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 16/3/21
 * Time: 18:10
 */

namespace Zan\Framework\Sdk\Log\Track;

use Zan\Framework\Foundation\Core\Path;
use Exception;
class TrackFile {

    private $path;
    private $postData;
    private $module;
    private $type;

    public function __constract($app, $module, $type, $path){
        $this->path = Path::getLogPath();
        if(!$path){
            throw new Exception('path not be null');
        }
        $this->path = $this->path."/".$path;
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            chmod($dir, 0755);
        }
        $this->module = $module;
        $this->type   = $type;
        return $this;
    }

    public function doWrite($log, $level){
        $this->getLogData($log, $level);
        file_put_contents($this->path, $this->postData, FILE_APPEND);
    }

    private function getLogData($log, $level){
        if(is_array($log)){
            $log = json_encode($log,JSON_UNESCAPED_UNICODE);
        }
        $this->postData .= sprintf("[%s]\t[%s]\t[%s]\t%s\t%s\n", date('Y-m-d H:i:s.u'), $level,  $this->module, $this->type, $log);
    }
}