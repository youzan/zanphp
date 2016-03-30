<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/3/1
 * Time: 下午6:03
 */
namespace Zan\Framework\Store\Database\Mysql;
use Zan\Framework\Store\Database\Mysql\Validator;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Store\Database\Mysql\Exception as MysqlException;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Core\ConfigLoader;
class SqlMap
{
    use Singleton;

    private $andNum = 20;
    private $maxDirDepth = 5;
    private $sqlMaps = [];
    private $sqlMap = [];
    const RESULT_TYPE_INSERT = 'insert';
    const RESULT_TYPE_UPDATE = 'update';
    const RESULT_TYPE_DELETE = 'delete';
    const RESULT_TYPE_ROW = 'row';
    const RESULT_TYPE_SELECT = 'select';
    const RESULT_TYPE_COUNT = 'count';
    const RESULT_TYPE_DEFAULT = 'default';

    public function init($sqlPath = '')
    {
        $this->setSqlMaps($sqlPath);
    }

    private function setSqlMaps($sqlPath = '')
    {
        $sqlPath = $sqlPath === '' ? Path::getSqlPath() : $sqlPath;
        $sqlMaps = ConfigLoader::getInstance()->loadDistinguishBetweenFolderAndFile($sqlPath);
        if (null == $sqlMaps || [] == $sqlMaps) {
            return;
        }
        foreach ($sqlMaps as $key => $sqlMap) {
            $sqlMap = $this->parseSqlMap($sqlMap, explode('.', $key), str_replace('.', '/', $key));
            $sqlMaps[$key] = $sqlMap;
        }
        $this->sqlMaps = $sqlMaps;
    }

    public function getSql($sid, $data = [], $options = [])
    {
        $this->sqlMap = $this->getSqlMapBySid($sid);
        $type = strtolower($this->getSqlType());
        $this->sqlMap['sql_type'] = $this->getSqlType();
        $this->sqlMap['sql'] = trim($this->sqlMap['sql']);
        switch ($type) {
            case 'insert' :
                $this->insert($data, $options);
                break;
            case 'update' :
                $this->update($data, $options);
                break;
            case 'delete' :
                $this->delete($data, $options);
                break;
            case 'select' :
                $this->select($data, $options);
                break;
        }
        return $this->sqlMap;
    }

    private function getSqlType()
    {
        preg_match('/^\s*(INSERT|SELECT|UPDATE|DELETE)/is', $this->sqlMap['sql'], $match);
        if (!$match) {
            throw new MysqlException('sql语句类型错误,必须是INSERT|SELECT|UPDATE|DELETE其中之一');
        }
        return trim($match[0]);
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
            $columns[] = $this->quotaColumn($column);
            $values[] = "'" . Validator::validate($value) . "'";
        }
        $insert  = '(' . implode(',', $columns) . ') values(';
        $insert .= implode(',', $values) . ')';
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'insert', $insert);

//        $this->splitTable($data);
        $this->getTable($data)
            ->addSqlLint($options);
        $this->formatSql();
        return $this;
    }

    private function delete($data, $options)
    {
        if (isset($data['where'])) {
            $this->checkRequire($data['where']);
        }
        $this->parseVars($data);
        $this->parseWhere($data);
        $this->parseAnds($data);
        $this->parseOr($data);
        $this->parseGroupBy($data);
        $this->parseOrderBy($data);
        $this->parseLimit($data);
        $this->formatSql();
        $this->getTable($data);
//        $this->splitTable($data);
        $this->addSqlLint($options);
        return $this;
    }

    private function update($data, $options)
    {
        if (isset($data['where'])) {
            $this->checkRequire($data['where']);
        }
        $this->parseVars($data);
        $this->parseWhere($data);
        $this->parseAnds($data);
        $this->parseOr($data);
        $this->parseData($data);
        $this->parseGroupBy($data);
        $this->parseOrderBy($data);
        $this->parseLimit($data);
        $this->getTable($data);
//        $this->splitTable($data);
        $this->addSqlLint($options);
        $this->formatSql();
        return $this;
    }

    private function checkDataKeys($data)
    {
        //todo
    }

    private function parseData($data)
    {
        if (!$data || !isset($data['data']) || count($data['data']) == 0) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'data', '');
            return $this;
        }
        $updateData = $data['data'];
        if (!isset($updateData[0])) {
            $tmp = [];
            foreach ($updateData as $column => $value) {
                $tmp[] = [$column, $value];
            }
            $updateData = $tmp;
        }
        if (count($updateData) == 0) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'data', '');
            return $this;
        }
        $clauses = [];
        foreach ($updateData as $row) {
            $expr = false;
            if (isset($row[2]) && $row[2]) {
                $expr = $row[2];
            }
            list($column, $value) = $row;
            if (false === $expr) {
                $clause = ' ' . $this->quotaColumn($column) . " = '" . Validator::validate($value) . "'";
            } else {
                $clause = ' ' . $this->quotaColumn($column) . " = " . Validator::validate($expr) . " ";
            }

            $clauses[] = $clause;
        }

        $updateDataSql = implode(',', $clauses);
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'data', $updateDataSql);
        return $this;
    }

    private function select($data, $options = [])
    {
        if (isset($data['where'])) {
            $this->checkRequire($data['where']);
        }
        $this->parseColumn($data);
        $this->parseVars($data);
        $this->parseWhere($data);
        $this->parseAnds($data);
        $this->parseOr($data);
        $this->parseGroupBy($data);
        $this->parseOrderBy($data);
        $this->parseLimit($data);
        $this->formatSql();
        $this->getTable($data);
//        $this->splitTable($data);
        $this->addSqlLint($options);
        return $this;
    }

    private function replaceSqlLabel($sql, $label, $string)
    {
        return str_replace('#' . strtoUpper($label) . '#', $string, $sql);
    }

    private function quotaColumn($col)
    {
        return '`'. str_replace('.', '`.`', $col) . '`';
    }


    private function getSqlMapBySid($sid)
    {
        $sidData = $this->parseSid($sid);
        $base = $sidData['base'];
        $filePath = $sidData['file_path'] ;
        $mapKey = $sidData['key'];
        $sqlMap = [];
        foreach ($base as $route) {
            if ([] == $sqlMap && !isset($this->sqlMaps[$route])) {
                break;
            }
            $sqlMap = [] == $sqlMap ? $this->sqlMaps[$route] : $sqlMap[$route];
        }
        if ([] != $sqlMap) {
            if (isset($sqlMap[$mapKey])) {
                return $sqlMap[$mapKey];
            }
            throw new MysqlException('no such sql key: ' . $sid);
        }

        $sqlMap = $this->getSqlFile($filePath);
        if (!$sqlMap || !isset($sqlMap[$mapKey])) {
            throw new MysqlException('no such sql: ' . $sid);
        }
        $this->sqlMaps[$filePath] = $this->parseSqlMap($sqlMap, $base, $filePath);
        return $this->sqlMaps[$filePath][$mapKey];
    }

    private function parseSid($sid)
    {
        $pos = strrpos($sid, '.');
        if (false === $pos) {
            throw new MysqlException('no such sql id');
        }

        $filePath = substr($sid, 0, $pos);
        $base = explode('.', $filePath);
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
            $expKey = explode('_', $key);
            $resultType = $expKey[0];
            if (in_array($resultType, [self::RESULT_TYPE_INSERT, self::RESULT_TYPE_UPDATE, self::RESULT_TYPE_DELETE, self::RESULT_TYPE_ROW, self::RESULT_TYPE_SELECT, self::RESULT_TYPE_COUNT])) {
                $sqlMap[$key]['result_type'] = $resultType;
            } else {
                $sqlMap[$key]['result_type'] = self::RESULT_TYPE_DEFAULT;
            }

            $sqlMap[$key]['key']  = $base . '.' . $key;
            if (!isset($row['require'])) {
                $sqlMap[$key]['require'] = [];
            }
            if (!isset($row['limit'])) {
                $sqlMap[$key]['limit'] = [];
            }
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
            $sqlMap[$key]['rw']   = 'w';

            if (preg_match('/^\s*select/i', $row['sql'])) {
                $sqlMap[$key]['rw'] = 'r';
            }
        }
        return $sqlMap;
    }

    private function getSqlFile($filePath)
    {
        return require Path::getSqlPath() . $filePath . '.php';
    }

    private function checkRequire($where)
    {
        $requireMap = [];
        $limitMap = [];
        if ($this->sqlMap['require']) {
            $requireMap = array_flip($this->sqlMap['require']);
        }
        if ($this->sqlMap['limit']) {
            $limitMap = array_flip($this->sqlMap['limit']);
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

    private function parseColumn($data)
    {
        if (!$data || !isset($data['column']) || count($data['column']) == 0) {
            $column = '*';
        } else {
            $column = $data['column'];
        }
        if (is_array($column)) {
            $column = implode(',', $column);
        }
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'column', $column);
        return $this;
    }

    private function parseVars($data)
    {
        if (!$data || !isset($data['var']) || count($data['var']) == 0) {
            return false;
        }
        $vars = $data['var'];
        $firstSearches = [];
        $secSearches = [];
        $replaces = [];
        foreach ($vars as $key => $value) {
            $firstSearches[] = '#' . strtoupper($key) . '#';
            $secSearches[] = '#{' . strtolower($key) . '}';
            if (is_array($value)) {
                $replaces[] = '(' . implode(',', $value) . ')';
            } else {
                $replaces[] = "'" . $value . "'";
            }
        }
        $this->sqlMap['sql'] = str_replace($firstSearches, $replaces, $this->sqlMap['sql']);
        $this->sqlMap['sql'] = str_replace($secSearches, $replaces, $this->sqlMap['sql']);
        return $this;
    }

    private function parseWhere($data, $or = false, $andLabel = '')
    {
        $where = (isset($data['where'])) ? $data['where'] : [];
        if (!is_array($where) || count($where) == 0) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'where', '');
            return $this;
        }

        $conditionWord = 'and';
        if (true === $or && '' === $andLabel) {
            $conditionWord = 'or';
        }
        $clauses = [];
        foreach ($where as $row) {
            $expr = false;
            if (isset($row[3]) && $row[3]) {
                $expr = $row[3];
            }
            list($column, $condition, $value) = $row;
            $condition = strtolower(trim($condition));
            $column = trim($column);
            if ('like' === $condition && '%%%' === trim($value)) {
                //todo throw 过滤%%%

            }

            if (false === $expr) {
                if('in' === $condition || 'not in' === $condition) {
                    $clause = $this->parseWhereIn($condition, $column, $value);
                } else {
                    $clause = self::quotaColumn($column) . ' ' . $condition . " '" . Validator::validate($value) . "' ";
                }
            } else {
                $clause = self::quotaColumn($column) . ' ' . $condition . ' ' . $expr . ' ';
            }
            $clauses[] = $clause;
        }

        $parseWhere = '';
        if ('' === $andLabel) {
            $parseWhere .= " $conditionWord ";
        }
        $parseWhere .= implode(" $conditionWord ", $clauses);

        if (false === $or) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'where', $parseWhere);
        } elseif ('' !== $andLabel) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], $andLabel, $parseWhere);
        } else {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'or', trim($parseWhere, ' or'));
        }
        return $this;
    }

    private function parseWhereIn($condition, $column, $value)
    {
        $value = is_string($value) ? explode(',',$value) : $value;
        if (!is_array($value) || count($value) == 0) {
            //todo throw sql where条件中in为空
        }
        $clause = $this->quotaColumn($column) . ' ' . $condition . ' (';
        $tmp = [];
        foreach ($value as $v) {
            $tmp[] = "'" . Validator::validate($v) . "'";
        }
        $clause .= implode(',', $tmp) . ') ';
        return $clause;
    }

    private function parseAnds($data)
    {
        for ($i = 0; $i < $this->andNum; $i++) {
            $andLabel = ($i === 0) ? "and" : "and" . $i;
            if (isset($data[$andLabel])) {
                $this->parseAnd($data[$andLabel], $andLabel);
            } else {
                break;
            }
        }
        return $this->removeAnd($this->sqlMap);
    }

    private function parseAnd($andData, $andLabel = "")
    {
        return $this->parseWhere(['where' => $andData], true, $andLabel);
    }

    private function removeAnd()
    {
        $this->sqlMap['sql'] = preg_replace('/#and\d#/i', '', $this->sqlMap['sql']);
        return $this;
    }

    private function parseOr($data)
    {
        $or = (isset($data['or'])) ? $data['or'] : [];
        return $this->parseWhere(['where' => $or], true);
    }

    private function parseOrderBy($data)
    {
        $order = '';
        if (isset($data['order']) && '' !== $data['order']) {
            $order = trim($data['order']);
        }
        if ('' != $order) {
            $order = ' order by ' . $order . ' ';
        }
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'order', $order);
        return $this;
    }

    private function parseGroupBy($data)
    {
        $group = '';
        if (isset($data['group']) && '' !== $data['group']) {
            $group = trim($data['group']);
        }
        if ('' != $group) {
            $group = ' group by ' . $group . ' ';
        }

        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'group', $group);
        return $this;
    }

    private function parseLimit($data)
    {
        $limit = '';
        if(isset($data['limit']) && '' !== $data['limit']) {
            $limit = trim($data['limit']);
        }
        if ('' != $limit) {
            $limit = ' limit ' . $limit . ' ';
        }
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'limit', $limit);
        return $this;
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
        return $this;
    }


}