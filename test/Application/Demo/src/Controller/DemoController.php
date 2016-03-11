<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/3
 * Time: 23:43
 */

namespace Com\Youzan\Demo\Controller;

use Zan\Framework\Foundation\Domain\Controller;

class DemoController extends Controller
{
    public function getIndexHtml($request, $context)
    {
        return $this->display('aaa');
    }
}