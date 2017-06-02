<?php

namespace Zan\Framework\Network\Http\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Exception\PageNotFoundException;
use Zan\Framework\Network\Http\Response\BaseResponse;
use Zan\Framework\Network\Http\Response\RedirectResponse;

class PageNotFoundHandler implements ExceptionHandler
{
    private $configKey = 'error';

    public function handle(\Exception $e)
    {
        if (!is_a($e, PageNotFoundException::class)) {
            return false;
        }
        $config = Config::get($this->configKey, null);
        if (!$config) {
            return false;
        }
        // 跳转到配置的404页面
        return RedirectResponse::create($config['404'], BaseResponse::HTTP_FOUND);
    }
}
