<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
namespace Zan\Framework\Store\Database\Sql;

use Zan\Framework\Contract\Store\Database\ResultTypeInterface;
use Zan\Framework\Store\Database\Sql\Exception\SqlCanNotFindTableNameException;
use Zan\Framework\Store\Database\Sql\Exception\SqlTypeException;

class SqlParser
{
    private $sqlMap;

    public function setSqlMap($sqlMap)
    {
        $this->sqlMap = $sqlMap;
        return $this;
    }

    public function parse()
    {
        foreach ($this->sqlMap as $key => $map) {
            if ($key == 'table') {
                continue;
            }
            $expKey = explode('_', $key);
            if (!isset($expKey[0])) {
                unset($this->sqlMap[$map]);
                continue;
            }
            $map['sql'] = trim($map['sql']);

            $map['require'] = isset($map['require']) ? $map['require'] : [];
            $map['limit'] = isset($map['limit']) ? $map['limit'] : [];
            $map['rw'] = 'w';
            if (preg_match('/^\s*select/i', $map['sql'])) {
                $map[$key]['rw'] = 'r';
            }

            $map['result_type'] = $this->checkResultType(strtolower($expKey[0]));
            $map['table']= $this->getTable($map);
            $map['sql_type'] = $this->getSqlType($map['sql']);
            $this->sqlMap[$key] = $map;
        }
        return $this;
    }

    public function getSqlMap()
    {
        return $this->sqlMap;
    }

    private function getSqlType($sql)
    {
        preg_match('/^\s*(INSERT|SELECT|UPDATE|DELETE)/is', $sql, $match);
        if (!$match) {
            throw new SqlTypeException('sql语句类型错误,必须是INSERT|SELECT|UPDATE|DELETE其中之一');
        }
        return strtolower(trim($match[0]));
    }

    private function checkResultType($mapKey)
    {
        switch ($mapKey) {
            case 'insert' :
                $resultType = ResultTypeInterface::LAST_INSERT_ID;
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

    private function getTable($map)
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
            throw new SqlCanNotFindTableNameException('Can not find table name, please check your sql type');
        }
        preg_match($tablePregMap[$type], $sql, $matches);
        if (!is_array($matches) || !isset($matches[0])) {
            throw new SqlCanNotFindTableNameException('Can not find table name, please check your sql type');
        }
        $table = $matches[0];
        //去除`符合和库名
        if (false !== ($pos = strrpos($table, '.'))) {
            $table = substr($table, $pos + 1);
        }
        $table = trim($table, '`');
        if ('' == $table || !strlen($table)) {
            throw new SqlCanNotFindTableNameException('Can\'t get table name');
        }
        return $table;
    }
}