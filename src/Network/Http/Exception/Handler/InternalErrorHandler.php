<?php

namespace Zan\Framework\Network\Http\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Core\RunMode;
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

        // prevent fatal error
        try {
            $config = Config::get($this->configKey, null);
            if (!$config) {
                if (Config::get('debug') && !RunMode::isOnline()) {
                    $errorInfo = [
                        'code' => 99999,
                        'msg' => $e->getMessage(),
                        'file' => $e->getFile() . ":" . $e->getLine(),
                        'trace' => $e->getTrace()
                    ];
                    return new Response($errorInfo);
                } else {
                    $code = $e->getCode();
                    return new Response("网络错误($code)");
                }
            }
            // 跳转到配置的500页面
            if (isset($config['500'])) {
                return RedirectResponse::create($config['500'], BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
            }

            $errMsg = '对不起，页面被霸王龙吃掉了... ';
            $errorPagePath = Path::getRootPath() . '/vendor/zanphp/zan/src/Foundation/View/Pages/Error.php';
            $errorPage = require $errorPagePath;
            return new Response($errorPage);
        } catch (\Throwable $t) {
            return $this->handle(t2ex($t));
        } catch (\Exception $e) {
            return $this->handle($e);
        }
    }
}
