<?php


namespace Zan\Framework\Network\Http\Exception\Handler;


use Zan\Framework\Contract\Foundation\ExceptionHandler;

class ForbiddenHandler implements ExceptionHandler
{

    /**
     * @param \Exception $e
     *  * \Thrift\Exception\TException
     *  * \Zan\Framework\Foundation\Exception\ZanException
     *      * \Zan\Framework\Foundation\Exception\SystemException
     *      * \Zan\Framework\Foundation\Exception\BusinessException
     *      * OtherZanExceptions
     *  * OtherExceptions
     *
     * @return mixed
     *  * bool
     */
    public function handle(\Exception $e)
    {
        // TODO: Implement handle() method.
    }
}