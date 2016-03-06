<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/6
 * Time: 23:30
 */

namespace Zan\Framework\Foundation\View;


class Tpl {
    public static function load($tpl, $data=null)
    {
        if(null !== $data) {
            extract($data);
        }

        require $tpl;
    }
}