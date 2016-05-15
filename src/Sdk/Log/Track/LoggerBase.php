<?php
/**
 * Created by PhpStorm.
 * User: haitao
 * Date: 16/3/17
 * Time: 13:33
 */

namespace Zan\Framework\Sdk\Log\Track;

class LoggerBase {
    protected $app;
    protected $module;
    protected $type;
    protected $level;
    protected $leveNum = 0;
    protected $path;
    protected $logMap =[
        'debug'     => 0,
        'info'      => 1,
        'notice'    => 2,
        'warning'   => 3,
        'error'     => 4,
        'critical'  => 5,
        'alert'     => 6,
        'emergency' => 7,
    ];

    /**
     * 初始化
     * @param $config
     */
    public function init($config){
        $this->app      = $config['app'];
        $this->module   = $config['module'];
        $this->type     = $config['type'];
        $this->level    = $config['level'];
        $this->levelNum = $this->getLevelNum($this->level);
        $this->path     = $config['path'];
    }

    /**
     * 检查等级
     * @param $funcLevel
     * @return bool
     */
    public function checkLevel($funcLevel){
        $funcLevelNum   = $this->getLevelNum($funcLevel);
        if($this->leveNum >= $funcLevelNum){
            return true;
        }
        return false;
    }

    /**
     * get level num
     * @param $level
     * @return mixed
     */
    private function getLevelNum($level){
        return $this->logMap[$level];
    }
} 