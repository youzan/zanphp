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
    }

}