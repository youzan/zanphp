<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/24
 * Time: 下午2:31
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

abstract class BaseLogger
{
    protected $config;

    public function __construct(array $config)
    {
        if (!$config) {
            throw new InvalidArgumentException('Config is required' . $config);
            return false;
        }
        $this->config = $config;
    }

    abstract public function write($log);

}
