<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/11
 * Time: 11:00
 */

namespace Zan\Framework\Network\Http\Exception\Handler;

use Thrift\Exception\TApplicationException;
use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Response\BaseResponse;
use Zan\Framework\Network\Http\Response\RedirectResponse;
use Zan\Framework\Network\Http\Response\Response;

class InternalErrorHandler implements ExceptionHandler
{
    private $configKey = 'error';

    public function handle(\Exception $e)
    {
        if (!is_a($e, \Exception::class)) {
            return false;
        }
        $config = Config::get($this->configKey, null);
        if (!$config) {
            return new Response('网络错误');
        }
        // 跳转到配置的500页面
        if (isset($config['500'])) {
            return RedirectResponse::create($config['500'], BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $errMsg = '对不起，页面被霸王龙吃掉了... ';
        $errorPagePath = Path::getRootPath() . '/vendor/zanphp/zan/src/Foundation/View/Pages/Error.php';
        $errorPage = require $errorPagePath;
        return new Response($errorPage);
    }
}
