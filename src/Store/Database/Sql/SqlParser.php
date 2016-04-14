<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/12
 * Time: 下午4:05
 */
namespace Zan\Framework\Store\Database\Sql;
use Zan\Framework\Contract\Store\Database\ResultTypeInterface;
class SqlParser
{

    public function getResultType($sqlMap)
    {
        foreach ($sqlMap as $key => $map) {
            $expKey = explode('_', $key);
            if (!isset($expKey[0])) {
                unset($sqlMap[$map]);
                continue;
            }
            $map['result_type'] = $this->checkResultType(strtolower($expKey[0]));
            $sqlMap[$key] = $map;
        }
        return $sqlMap;
    }

    private function checkResultType($mapKey)
    {
        switch ($mapKey) {
            case 'insert' :
                $resultType = ResultTypeInterface::INSERT;
                break;
            case 'update' :
                $resultType = ResultTypeInterface::UPDATE;
                break;
            case 'delete' :
                $resultType = ResultTypeInterface::DELETE;
                break;
            case 'row' :
                $resultType = ResultTypeInterface::ROW;
                break;
            case 'select' :
                $resultType = ResultTypeInterface::SELECT;
                break;
            case 'batch' :
                $resultType = ResultTypeInterface::BATCH;
                break;
            case 'count' :
                $resultType = ResultTypeInterface::COUNT;
                break;
            case 'raw' :
                $resultType = ResultTypeInterface::RAW;
                break;
            default :
                $resultType = ResultTypeInterface::RAW;
                break;
        }
        return $resultType;
    }

    public function getTable($map)
    {
        //正则匹配数据表名，表名中不能有空格
        $tablePregMap = [
            'INSERT' => '/(?<=\sINTO\s)\S*/i',
            'SELECT' => '/(?<=\sFROM\s)\S*/i',
            'DELETE' => '/(?<=\sFROM\s)\S*/i',
            'UPDATE' => '/(?<=UPDATE\s)\S*/i',
            'REPLACE'=> '/(?<=REPLACE\s)\S*/i'
        ];
        if (isset($map['table']) && '' !== $map['table']) {
            return $map;
        }
        $sql = $map['sql'];
        $type = strtoupper(substr($sql, 0, strpos($sql, ' ')));
        $matches = null;
        if (!isset($tablePregMap[$type])) {
            //todo throw 'Can not find table name, please check your sql type'
        }
        preg_match($tablePregMap[$type], $sql, $matches);
        if (!is_array($matches) || !isset($matches[0])) {
            //todo throw 'Can not find table name, please check your sql type'
        }
        $table = $matches[0];
        //去除`符合和库名
        if (false !== ($pos = strrpos($table, '.'))) {
            $table = substr($table, $pos + 1);
        }
        $table = trim($table, '`');
        if ('' == $table || !strlen($table)) {
            //todo throw "Can't get table name"
        }
        $map['table'] = $table;
        return $map;
    }









}