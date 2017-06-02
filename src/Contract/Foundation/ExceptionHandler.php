<?php

namespace Zan\Framework\Contract\Foundation;


interface ExceptionHandler
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
    public function handle(\Exception $e);
}