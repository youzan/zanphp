<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/9
 * Time: 10:59
 */

namespace Zan\Framework\Network\Http\Exception;


use Zan\Framework\Foundation\Exception\BusinessException;

class RedirectException extends BusinessException
{
    public $redirectUrl = [
        'domain' => 'zan',
        'path' => '/',
    ];
}