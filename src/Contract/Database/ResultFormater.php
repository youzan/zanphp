<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 21:14
 */

namespace Zan\Framework\Contract\Database;


interface ResultFormater
{
    /**
     * ResultFormater constructor.
     * @param DbResult $result
     * @param int $resultType
     */
    public function __construct(DbResult $result, $resultType=ResultType::RAW);

    /**
     * @return mixed(base on ResultType)
     */
    public function format(); 
}