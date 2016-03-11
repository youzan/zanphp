<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/6
 * Time: 23:30
 */

namespace Zan\Framework\Foundation\View;


class Tpl
{
    public static function load($path, $data = null, $assignTplBasePathForTest = '')
    {
        $path = self::getTplFullPath($path, $assignTplBasePathForTest);
        if(null !== $data) {
            extract($data);
        }
        require $path;
    }

    public static function getTplFullPath($path, $assignTplBasePathForTest)
    {
        if($assignTplBasePathForTest) {
            return $assignTplBasePathForTest . '/' . $path . '.html';
        }
        if(!preg_match('/^static./i', $path)){
            $path = explode('/', $path);
            $mod = array_shift($path);
            return APP_PATH . $mod . '/views/' . join('/', $path) . '.html';
        }
        $path = substr($path,7);
        $path = COMMON_STATIC_PATH  .  $path . '.html';
        return $path;
    }
}