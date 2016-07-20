<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/5/31
 * Time: 上午11:29
 */

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Sdk\Cdn\Qiniu;

class InitializeQiniuConfig
{
    public function bootstrap($server)
    {
        $config = Config::get('qiniu');
        Qiniu::setConfig($config);
    }
}