<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/7
 * Time: 20:22
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Domain\Controller as BaseController;

class Controller extends BaseController {
    public function display($tpl)
    {
        $data = [];
        yield new ViewResponse($tpl, $data);
    }

    public function r($code, $msg, $data)
    {
        yield new JsonResponse($code,$msg,$data);
    }
}