<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/3/1
 * Time: 下午6:03
 */
namespace Zan\Framework\Store\Database\Mysql;
use Zan\Framework\Store\Database\Mysql\Validator;

class SqlMap
{
    private $sqlMaps = [];
    private $sqlMap = [];
    private $andNum = 20;

    private function loadAllSqlFiles($sqlPath = '')
    {
        $sqlPath = $sqlPath === '' ? SQL_PATH : $sqlPath;
    }

    public function setSqlMaps()
    {
        $sqlMaps = $this->loadAllSqlFiles();
        $this->sqlMaps = $sqlMaps;
        return $this;
    }

    public function getSql($sid, $data = [], $options = [])
    {
        $this->sqlMap = $this->getSqlMapBySid($sid);
        $type = strtolower($this->getSqlType());
        switch ($type) {
            case 'insert' :
                $this->insert($data, $options);
                break;
            case 'update' :
                $this->sqlMap = $this->update($this->sqlMap, $data, $options);
                break;
            case 'delete' :
                $this->sqlMap = $this->delete($this->sqlMap, $data, $options);
                break;
            case 'select' :
                $this->sqlMap = $this->select($this->sqlMap, $data, $options);
                break;
        }
        $this->sqlMap['sql'] = Validator::validate($this->sqlMap['sql']);
        return $this->sqlMap;
    }

    private function getSqlType()
    {
        preg_match('/^\s*(INSERT|SELECT|UPDATE|DELETE)/is', $this->sqlMap['sql'], $match);
        if (!$match) {
            //todo throw type error
        }
        return $match[0];
    }

    private function insert($data, $options)
    {
        $insertData = (isset($data['insert'])) ? $data['insert'] : [];
        if (!is_array($insertData) || count($insertData) == 0) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'insert', '');
            return $this;
        }
        $columns = [];
        $values = [];
        foreach ($insertData as $column => $value) {
            $fCol = $this->formatColumn($column);
            $this->sqlMap['bind'][$fCol] = $value;
            $columns[] = $this->quotaColumn($column);
            $values[] = $fCol;
        }
        $insert  = '(' . implode(',', $columns) . ') values(';
        $insert .= implode(',', $values) . ')';
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'insert', $insert);

//        $this->splitTable($sqlMap, $data);
        $this->getTable($data)
            ->addSqlLint($options);
        $this->formatSql();
        $this->sqlMap['sql_type'] = 'INSERT';
        return $this;
    }

    private function delete($sqlMap, $data, $options)
    {
        $this->checkRequire($sqlMap, $data['where']);
        $sqlMap = $this->parseVars($sqlMap, $data);
        $sqlMap = $this->parseWhere($sqlMap, $data);
        $sqlMap = $this->parseAnds($sqlMap, $data);
        $sqlMap = $this->parseOr($sqlMap, $data);
        $sqlMap = $this->parseOrderBy($sqlMap, $data);
        $sqlMap = $this->parseGroupBy($sqlMap, $data);
        $sqlMap = $this->parseLimit($sqlMap, $data);
        $sqlMap = $this->parseBind($sqlMap, $data);
        $sqlMap = $this->formatSql($sqlMap);
        $sqlMap = $this->getTable($sqlMap, $data);
//        $this->splitTable($sqlMap, $data);
        $sqlMap = $this->addSqlLint($sqlMap, $options);
        return $sqlMap;
    }

    private function update($sqlMap, $data, $options)
    {
        $this->checkRequire($sqlMap, $data['where']);
        $sqlMap = $this->parseVars($sqlMap, $data);
        $sqlMap = $this->parseWhere($sqlMap, $data);
        $sqlMap = $this->parseAnds($sqlMap, $data);
        $sqlMap = $this->parseOr($sqlMap, $data);
        $sqlMap = $this->parseData($sqlMap, $data);
        $sqlMap = $this->parseOrderBy($sqlMap, $data);
        $sqlMap = $this->parseGroupBy($sqlMap, $data);
        $sqlMap = $this->parseLimit($sqlMap, $data);
        $sqlMap = $this->parseBind($sqlMap, $data);
        $sqlMap = $this->formatSql($sqlMap);
        $sqlMap = $this->getTable($sqlMap, $data);
//        $this->splitTable($sqlMap, $data);
        $sqlMap = $this->addSqlLint($sqlMap, $options);
        return $sqlMap;
    }


    private function parseData($sqlMap, $data)
    {
        if (!$data || !isset($data['data']) || count($data['data']) == 0) {
            $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'data', '');
            return $sqlMap;
        }
        $updateData = $data['data'];
        if (!isset($updateData[0])) {
            $tmp = [];
            foreach ($data as $column => $value) {
                $tmp[] = [$column, $value];
            }
            $updateData = $tmp;
        }
        if (count($updateData) == 0) {
            $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'data', '');
            return $sqlMap;
        }
        $updateDataSql = $this->parseWsData($updateData);
        $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'data', $updateDataSql);
        return $sqlMap;
    }

    private function parseWsData($data)
    {
        $clauses = [];
        foreach ($data as $row) {
            $expr = false;
            if (isset($row[2]) && $row[2]) {
                $expr = $row[2];
            }
            list($column, $value) = $row;
            $fCol = $this->formatColumn($column);
            if (false === $expr) {
                $clause = ' ' . $this->quotaColumn($column) . ' = ' . $fCol . ' ';
            } else {
                $clause = ' ' . $this->quotaColumn($column) . ' = ' . $expr . ' ';
            }

            if(false !== $value){
                $this->sqlMap['bind'][$fCol] = $value;
            }
            $clauses[] = $clause;
        }

        return implode(',',$clauses);
    }

    private function select($sqlMap, $data, $options = [])
    {
        $this->checkRequire($sqlMap, $data['where']);
        $sqlMap = $this->parseColumn($sqlMap, $data);
        $sqlMap = $this->parseVars($sqlMap, $data);
        $sqlMap = $this->parseWhere($sqlMap, $data);
        $sqlMap = $this->parseAnds($sqlMap, $data);
        $sqlMap = $this->parseOr($sqlMap, $data);
        $sqlMap = $this->parseOrderBy($sqlMap, $data);
        $sqlMap = $this->parseGroupBy($sqlMap, $data);
        $sqlMap = $this->parseLimit($sqlMap, $data);
        $sqlMap = $this->parseBind($sqlMap, $data);
        $sqlMap = $this->formatSql($sqlMap);
        $sqlMap = $this->getTable($sqlMap, $data);
//        $this->splitTable($sqlMap, $data);
        $sqlMap = $this->addSqlLint($sqlMap, $options);
        return $sqlMap;
    }

    private function replaceSqlLabel($sql, $label, $string)
    {
        return str_replace('#' . strtoUpper($label) . '#', $string, $sql);
    }

    private function formatColumn($column, $num = null)
    {
        $str = ':' . trim(str_replace('.' , '_', $column));

        if (null !== $num) {
            $str .= '_' . $num;
        }
        if (isset($this->sqlMap['bind'][$str])) {
            $str .= '_' . (string)rand(0,10);
        }
        return $str;
    }

    private function quotaColumn($col)
    {
        return '`'. str_replace('.', '`.`', $col) . '`';
    }


    private function getSqlMapBySid($sid)
    {
        $sidData = $this->parseSid($sid);
        $base = $sidData['base'];
        $filePath = $sidData['file_path'];
        $mapKey = $sidData['key'];
        if (isset($this->sqlMaps[$filePath])) {
            if (isset($this->sqlMaps[$filePath][$mapKey])) {
                return $this->sqlMaps[$filePath][$mapKey];
            }
            //todo throw error
        }
        $sqlMap = $this->getSqlFile($filePath);
        if (!$sqlMap || !isset($sqlMap[$mapKey])) {
            //todo throw error
        }
        $this->sqlMaps[$filePath] = $this->parseSqlMap($sqlMap, $base, $filePath);
        return $this->sqlMaps[$filePath][$mapKey];
    }

    private function parseSid($sid)
    {
        $pos = strrpos($sid, '.');
        if (false === $pos) {
            //todo throw sid error
        }

        $filePath = substr($sid, 0, $pos);
        $base = $filePath;
        $filePath = str_replace('.', '/', $filePath);

        return [
            'file_path' => $filePath,
            'base'      => $base,
            'key'       => substr($sid,$pos + 1),
        ];
    }

    private function parseSqlMap($sqlMap, $base, $filePath)
    {
        foreach ($sqlMap as $key => $row) {
            if ('table' === $key) {
                continue;
            }
            $sqlMap[$key]['key']  = $base . '.' . $key;
            if (!isset($row['require'])) {
                $sqlMap[$key]['require'] = [];
            }
            if (!isset($row['limit'])) {
                $sqlMap[$key]['limit'] = [];
            }

            //todo connection 是否放入sql map中
//            if (!isset($row['connection']) && isset($sqlMap['common']['connection'])) {
//                $sqlMap[$key]['connection'] = $sqlMap['common']['connection'];
//            }

            if (!isset($row['distribute']) && isset($sqlMap['common']['distribute'])) {
                $sqlMap[$key]['distribute'] = $sqlMap['common']['distribute'];
            }

            if (isset($row['join'])) {
                foreach ($row['join'] as $k => $j) {
                    if (false === strpos($j[1], '.')) {
                        $path = str_replace('/', '.', $filePath);
                        $sqlMap[$key]['join'][$k][1] = $path . '.' . $j[1];
                    }
                }
            }
//            if (!isset($sqlMap[$key]['connection']) || empty($sqlMap[$key]['connection'])) {
//
//            }
            $sqlMap[$key]['bind'] = [];
            $sqlMap[$key]['rw']   = 'w';

            if (preg_match('/^\s*select/i', $row['sql'])) {
                $sqlMap[$key]['rw'] = 'r';
            }
        }
        return $sqlMap;
    }

    private function getSqlFile($filePath)
    {
        //todo SQL_PATH
        return require SQL_PATH . $filePath . '.php';
    }

    private function checkRequire($sqlMap, $where)
    {
        $requireMap = [];
        $limitMap = [];
        if ($sqlMap['require']) {
            $requireMap = array_flip($sqlMap['require']);
        }
        if ($sqlMap['limit']) {
            $limitMap = array_flip($sqlMap['limit']);
        }

        if (count($requireMap) == 0 && count($limitMap) == 0) {
            return true;
        }

        foreach($where as $row) {
            $col = $row[0];
            if (count($requireMap) > 0) {
                if (isset($requireMap[$col]) ) {
                    unset($requireMap[$col]);
                }
            }
            if (count($limitMap) > 0) {
                if (!isset($limitMap[$col]) ) {
                    //todo throw limit error
                }
            }
        }
        if (count($requireMap) > 0) {
            //todo throw require error
        }
        return true;
    }

    private function parseColumn($sqlMap, $data)
    {
        if (!$data || !isset($data['column']) || count($data['column']) == 0) {
            $column = '*';
        } else {
            $column = $data['column'];
        }

        if (is_array($data)) {
            $column = implode(',', $data);
        }
        $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'column', $column);
        return $sqlMap;
    }

    private function parseVars($sqlMap, $data)
    {
        if (!$data || !isset($data['var']) || count($data['var']) == 0) {
            return false;
        }
        $vars = $data['var'];
        $searches  = [];
        $replaces = [];
        foreach($vars as $key => $value){
            $searches[]   = '#' .strtoupper($key) . '#';
            $replaces[]  = $value;
        }
        $sqlMap['sql'] = str_replace($searches, $replaces, $sqlMap['sql']);
        return $sqlMap;
    }

    private function parseWhere($sqlMap, $data, $or = false, $andLabel = '')
    {
        $where = (isset($data['where'])) ? $data['where'] : [];
        if (!is_array($where) || count($where) == 0) {
            $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'where', '');
            return $sqlMap;
        }

        $conditionWord = 'and';
        if (true === $or && '' === $andLabel) {
            $conditionWord = 'or';
        }
        $clauses = [];
        $columnMap  = [];
        foreach ($where as $row) {
            $expr = false;
            if (isset($row[3]) && $row[3]) {
                $expr = $row[3];
            }
            //TODO warning...
            list($column, $condition, $value) = $row;
            $condition = strtolower(trim($condition));
            $column = trim($column);
            if (!isset($columnMap[$column])) {
                $columnMap[$column] = 1;
                $fCol = $this->formatColumn($column);
            } else{
                $fCol = $this->formatColumn($column, $columnMap[$column]++);
            }

            if ('like' === $condition && '%%%' === trim($value)) {
                //todo throw 过滤%%%

            }

            if (false === $expr) {
                if('in' === $condition || 'not in' === $condition) {
                    $value = is_string($value) ? explode(',',$value) : $value;
                    if (!is_array($value) || count($value) == 0) {
                        //todo throw sql where条件中in为空
                    }
                    $clause = $this->quotaColumn($column) . ' ' . $condition . ' (';
                    $tmp = [];
                    foreach ($value as $key => $v) {
                        $fCol = $this->formatColumn($column, $key);
                        $tmp[] = $fCol;
                        $this->sqlMap['bind'][$fCol] = $v;
                    }
                    $clause .= implode(',', $tmp) . ') ';
                    $value = false;
                } else {
                    $clause = self::quotaColumn($column) . ' ' . $condition . ' ' . $fCol . ' ';
                }
            } else {
                $clause = self::quotaColumn($column) . ' ' . $condition . ' ' . $expr . ' ';
            }

            if (false !== $value) {
                $this->sqlMap['bind'][$fCol] = $value;
            }
            $clauses[] = $clause;
        }

        $parseWhere = '';
        if ('' === $andLabel) {
            $parseWhere .= " $conditionWord ";
        }
        $parseWhere .= implode(" $conditionWord ", $clauses);

        if (false === $or) {
            $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'where', $parseWhere);
        } elseif ('' !== $andLabel) {
            $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], $andLabel, $parseWhere);
        } else {
            $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'or', trim($parseWhere, ' or'));
        }
        return $sqlMap;
    }

    private function parseAnds($sqlMap, $data)
    {
        for ($i = 0; $i < $this->andNum; $i++) {
            $andLabel = ($i === 0) ? "and" : "and" . $i;
            if (isset($data[$andLabel])) {
                $sqlMap = $this->parseAnd($sqlMap, $data[$andLabel], $andLabel);
            } else {
                break;
            }
        }
        return $this->removeAnd($sqlMap);
    }

    private function parseAnd($sqlMap, $andData, $andLabel = "")
    {
        return $this->parseWhere($sqlMap, ['where' => $andData], true, $andLabel);
    }

    private function removeAnd($sqlMap)
    {
        $sqlMap['sql'] = preg_replace('/#and\d#/i', '', $sqlMap['sql']);
        return $sqlMap;
    }

    private function parseOr($sqlMap, $data)
    {
        $or = (isset($data['or'])) ? $data['or'] : [];
        return $this->parseWhere($sqlMap, ['where' => $or], true);
    }

    private function parseOrderBy($sqlMap, $data)
    {
        $order = '';
        if (isset($data['order']) && '' !== $data['order']) {
            $order = trim($data['order']);
        }
        if ('' != $order) {
            $order = ' order by ' . $order . ' ';
        }
        $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'order', $order);
        return $sqlMap;
    }

    private function parseGroupBy($sqlMap, $data)
    {
        $group = '';
        if (isset($data['group']) && '' !== $data['group']) {
            $group = trim($data['group']);
        }
        if ('' != $group) {
            $group = ' group by ' . $group . ' ';
        }

        $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'group', $group);
        return $sqlMap;
    }

    private function parseLimit($sqlMap, $data)
    {
        $limit = '';
        if(isset($data['limit']) && '' !== $data['limit']) {
            $limit = trim($data['limit']);
        }
        if ('' != $limit) {
            $limit = ' limit ' . $limit . ' ';
        }
        $sqlMap['sql'] = $this->replaceSqlLabel($sqlMap['sql'], 'limit', $limit);
        return $sqlMap;
    }

    private function parseBind($sqlMap, $data)
    {
        if (!isset($data['bind'])) {
            return $sqlMap;
        }

        foreach($data['bind'] as $key => $value){
            $key = ':' . $key;
            $sqlMap['bind'][$key] = $value;
        }
        return $sqlMap;
    }

    private function getTable($data)
    {
        $tablePregMap = [     //正则匹配数据表名，表名中不能有空格
            'INSERT' => '/(?<=\sINTO\s)\S*/i',
            'SELECT' => '/(?<=\sFROM\s)\S*/i',
            'DELETE' => '/(?<=\sFROM\s)\S*/i',
            'UPDATE' => '/(?<=UPDATE\s)\S*/i',
            'REPLACE'=> '/(?<=REPLACE\s)\S*/i'
        ];
        $table = '';
        if (isset($data['table']) && '' !== $data['table']) {
            $table = trim($data['table']);
        } else {
            $sql = $this->sqlMap['sql'];
            $type = strtoupper(substr($sql, 0, strpos($sql, ' ')));
            $matches = null;

            if (!isset($tablePregMap[$type])) {
                //todo throw Can not find sql type
            }
            preg_match($tablePregMap[$type], $sql, $matches);
            if (!is_array($matches) || !isset($matches[0])) {
                //todo throw Can not find sql type
            }
            $table = $matches[0];
            //去除`符合和库名
            if (false !== ($pos = strrpos($table, '.'))) {
                $table = substr($table, $pos + 1);
            }
            $table = trim($table, '`');
        }

        if ('' == $table || !strlen($table)) {
            //todo throw Can not get table name
        }
        $this->sqlMap['table'] = $table;
        return $this;
    }

    //判断该表是否需要分表
    private function splitTable($data)
    {
        //todo
    }

    private function addSqlLint($options)
    {
        $this->sqlMap = array_merge($this->sqlMap, $options);

        if (isset($this->sqlMap['use_master']) && $this->sqlMap['use_master']) {
            $this->sqlMap['sql'] = "/*master*/" . $this->sqlMap['sql'];
        }
        return $this;
    }


    private function formatSql()
    {
        $sql = trim($this->sqlMap['sql']);
        $sql = str_replace("\n", NULL, $sql);
        $sql = str_replace("\r", NULL, $sql);
        $sql = preg_replace('/\s+/', " ", $sql);

        $this->sqlMap['sql'] = $sql;
        $this->bindValue();
        return $this;
    }

    private function bindValue()
    {
        if (!isset($this->sqlMap['bind']) || !is_array($this->sqlMap['bind']) || count($this->sqlMap['bind']) == 0) {
            return false;
        }
        foreach ($this->sqlMap['bind'] as $bind => $value) {
            $this->sqlMap['sql'] = str_replace($bind, (string)$value, $this->sqlMap['sql']);
        }
        return true;
    }

}