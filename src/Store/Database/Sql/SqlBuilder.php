<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/12
 * Time: 下午4:05
 */
namespace Zan\Framework\Store\Database\Sql;

class SqlBuilder
{
    private $sql;

    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function builder($data)
    {

    }

    private function select($data)
    {

    }

    private function insert()
    {
        if (isset($data['inserts'])) {
            return $this->batchInserts($data);
        }
        $insert = isset($data['insert']) ? $data['insert'] : [];
        if (!is_array($insert) || count($insert) == 0) {
            $this->sql = $this->replaceSqlLabel($this->sql, 'insert', '');
            return $this;
        }

        $columns = [];
        $values = [];
        foreach ($insert as $column => $value) {
            $columns[] = $this->quotaColumn($column);
            $values[] = $this->parseValueType($value);
        }
        $replace = '(' . implode(',', $columns) . ') values(';
        $replace .= implode(',', $values) . ')';
        $this->sql = $this->replaceSqlLabel($this->sql, 'insert', $replace);
        return $this;
    }

    private function batchInserts($data)
    {
        $inserts = isset($data['inserts']) ? $data['inserts'] : [];
        if (!is_array($inserts) || count($inserts) == 0) {
            $this->sql = $this->replaceSqlLabel($this->sql, 'inserts', '');
            return $this;
        }

        $insertsArr = [];
        $cloumns = array_keys($inserts[0]);
        $replace  = '(' . implode(',', $cloumns) . ') values ';
        foreach ($inserts as $insert) {
            $values = [];
            foreach ($insert as $value) {
                $values[] = $this->parseValueType($value);
            }
            $insertsArr[] = '(' . implode(',', $values) . ')';
        }
        $replace .= implode(',', $insertsArr);
        $this->sql = $this->replaceSqlLabel($this->sql, 'inserts', $replace);
        return $this;
    }

    private function update($data)
    {
        $this->parseUpdateData($data);
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
        return $this;
    }

    private function delete($data)
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
        return $this;
    }

    private function quotaColumn($column)
    {
        return '`'. str_replace('.', '`.`', $column) . '`';
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
                    //todo throw sql map limit error
                }
            }
        }
        if (count($requireMap) > 0) {
            //todo throw 'sql map require error'
        }
        return true;
    }

    private function parseUpdateData($data)
    {
        if (!$data || !isset($data['data']) || [] == $data['data']) {
            $this->sql = $this->replaceSqlLabel($this->sql, 'data', '');
            return $this;
        }

        $update = $data['data'];
        if (!isset($update[0])) {
            $tmp = [];
            foreach ($update as $column => $value) {
                $tmp[] = [$column, $value];
            }
            $update = $tmp;
        }
        if (count($update) == 0) {
            $this->sql = $this->replaceSqlLabel($this->sql, 'data', '');
            return $this;
        }

        $clauses = [];
        foreach ($update as $row) {
            $expr = false;
            if (isset($row[2]) && '' != $row[2]) {
                $expr = $row[2];
            }
            list($column, $value) = $row;
            $clause = ' ' . $this->quotaColumn($column);
            $clause .= false === $expr ? " = '" . $value . "'" : " = " . $expr . " ";
            $clauses[] = $clause;
        }
        $replace = implode(',', $clauses);
        $this->sql = $this->replaceSqlLabel($this->sql, 'data', $replace);
        return $this;
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

    private function parseCount($data)
    {
        if (!$data || !isset($data['count']) || '' == $data['count']) {
            throw new MysqlException('what field do you want count?');
        }
        if (!is_string($data['count'])) {
            $count = 'count(*) as count_sql_rows';
        } else {
            $count = 'count(' . $data['count'] .') as count_sql_rows';
        }
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'count', $count);
        return $this;
    }

    private function parseVars($data)
    {
        if (!$data || !isset($data['var']) || [] == $data['var']) {
            return $this;
        }
        $vars = $data['var'];
        $firstLabels = [];
        $secLabels = [];
        $replaces = [];
        foreach ($vars as $key => $value) {
            $firstLabels[] = '#' . strtoupper($key) . '#';
            $secLabels[] = '#{' . strtolower($key) . '}';
            if (is_array($value)) {
                $replaces[] = '(' . implode(',', array_map([$this, 'parseValueType'], $value)) . ')';
            } else {
                $replaces[] = $this->parseValueType($value);
            }
        }
        $this->sql = str_replace($firstLabels, $replaces, $this->sql);
        $this->sql = str_replace($secLabels, $replaces, $this->sql);
        return $this;
    }

    private function parseValueType($value)
    {
        return is_int($value) ? $value : "'" . $value . "'";
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
                throw new MysqlException('sql like can not contain %%%');
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
            throw new MysqlException('sql where条件中in为空');
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
        return $this->removeAnd();
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


    //判断该表是否需要分表
    private function splitTable($data)
    {
        //todo
    }

    private function replaceSqlLabel($sql, $label, $string)
    {
        return str_replace('#' . strtoUpper($label) . '#', $string, $sql);
    }
}