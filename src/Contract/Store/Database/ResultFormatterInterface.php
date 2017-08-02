<?php

namespace Zan\Framework\Contract\Store\Database;


interface ResultFormatterInterface
{
    /**
     * ResultFormatterInterface constructor.
     * @param DbResultInterface $result
     * @param int $resultType
     */
    public function __construct(DbResultInterface $result, $resultType = ResultTypeInterface::RAW);

    /**
     * @return mixed(base on ResultType)
     */
    public function format();
}