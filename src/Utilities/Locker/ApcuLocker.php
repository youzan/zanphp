<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 8/9/16
 * Time: 3:23 PM
 */

namespace Zan\Framework\Utilities\Locker;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class ApcuLocker
{
    private $key = null;

    public function __construct($key)
    {
        if (!$key) {
            throw new InvalidArgumentException('ApcuLocker key is required!', 10002);
        }

        $this->key = $key;
        apcu_add($this->key, 0);
    }

    public function isLocked()
    {
        return !!apcu_fetch($this->key);
    }

    public function lock()
    {
        return apcu_cas($this->key, 0, 1);
    }

    public function unlock()
    {
        return apcu_cas($this->key, 1, 0);
    }

}
