<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/11
 * Time: 下午8:16
 */

namespace Zan\Framework\Foundation\View;


class BaseLoader
{
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