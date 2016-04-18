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

    public function builder($data, $sqlType, $require, $limit)
    {
        switch ($sqlType) {
            case 'select' :
                $this->select($data, $require, $limit);
                break;
            case 'insert' :
                $this->insert($data);
                break;
            case 'update' :
                $this->update($data, $require, $limit);
                break;
            case 'delete' :
                $this->delete($data, $require, $limit);
                break;
        }
        return $this;
    }

    private function select($data, $require, $limit)
    {
        $this->checkRequire($data, $require, $limit);
        $this->parseColumn($data);
        if (isset($data['count'])) {
            $this->parseCount($data);
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

    private function parseCount($data)
    {
        if (!$data || !isset($data['count']) || '' == $data['count']) {
            //todo throw 'what field do you want count?'
        }
        $count = 'count(' . $data['count'] . ') as count_sql_rows';
        $this->sql = $this->replaceSqlLabel($this->sql, 'count', $count);
        return $this;
    }

    private function insert($data)
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
            $columns[] = $this->formatColumn($column);
            $values[] = $this->formatValue($value);
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
                $values[] = $this->formatValue($value);
            }
            $insertsArr[] = '(' . implode(',', $values) . ')';
        }
        $replace .= implode(',', $insertsArr);
        $this->sql = $this->replaceSqlLabel($this->sql, 'inserts', $replace);
        return $this;
    }

    private function update($data, $require, $limit)
    {
        $this->parseUpdateData($data);
        $this->checkRequire($data, $require, $limit);
        $this->parseVars($data);
        $this->parseWhere($data);
        $this->parseAnds($data);
        $this->parseOr($data);

        $this->parseGroupBy($data);
        $this->parseOrderBy($data);
        $this->parseLimit($data);
        return $this;
    }

    private function delete($data, $require, $limit)
    {
        $this->checkRequire($data, $require, $limit);
        $this->parseVars($data);
        $this->parseWhere($data);
        $this->parseAnds($data);
        $this->parseOr($data);

        $this->parseGroupBy($data);
        $this->parseOrderBy($data);
        $this->parseLimit($data);
        return $this;
    }

    private function formatColumn($column)
    {
        return '`'. str_replace('.', '`.`', $column) . '`';
    }

    private function formatValue($value)
    {
        return is_int($value) ? $value : "'" . $value . "'";
    }

    private function checkRequire($data, $require, $limit)
    {
        if (!isset($data['where']) && !isset($data['and']) && !isset($data['or'])) {
            return true;
        }
        $where = isset($data['where']) ? $data['where'] : [];
        $where = isset($data['and']) ? array_merge($where, $data['and']) : $where;
        $where = isset($data['or']) ? array_merge($where, $data['or']) : $where;

        $requireMap = [];
        $limitMap = [];
        if (is_array($require) && [] != $require) {
            $requireMap = array_flip($require);
        }
        if (is_array($limit) && [] != $limit) {
            $limitMap = array_flip($limit);
        }
        if ([] == $requireMap && [] == $limitMap) {
            return true;
        }

        foreach($where as $row) {
            $col = $row[0];
            if (count($requireMap) > 0) {
                if (isset($requireMap[$col])) {
                    unset($requireMap[$col]);
                }
            }
            if (count($limitMap) > 0) {
                if (!isset($limitMap[$col])) {
                    //todo throw sql map limit error
                }
            }
        }
        if (count($requireMap) > 0) {
            //todo throw 'sql map require error'
        }
        return true;
    }

    private function parseColumn($data)
    {
        if (!$data || !isset($data['column']) || [] == $data['column'] || '' == $data['column']) {
            $column = '*';
        } else {
            $column = $data['column'];
        }
        $column = is_array($column) ? implode(',', $column) : $column;
        $this->sql = $this->replaceSqlLabel($this->sql, 'column', $column);
        return $this;
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
            $clause = ' ' . $this->formatColumn($column);
            $clause .= false === $expr ? " = '" . $value . "'" : " = " . $expr . " ";
            $clauses[] = $clause;
        }
        $replace = implode(',', $clauses);
        $this->sql = $this->replaceSqlLabel($this->sql, 'data', $replace);
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
                $replaces[] = '(' . implode(',', array_map([$this, 'formatValue'], $value)) . ')';
            } else {
                $replaces[] = $this->formatValue($value);
            }
        }
        $this->sql = str_replace($firstLabels, $replaces, $this->sql);
        $this->sql = str_replace($secLabels, $replaces, $this->sql);
        return $this;
    }



    private function parseWhere($data)
    {
        $where = isset($data['where']) ? $data['where'] : [];
        if (!is_array($where) || [] == $where) {
            $this->sql = $this->replaceSqlLabel($this->sql, 'where', '');
            return $this;
        }
        $parseWhere = $this->parseWhereStyleData($where, 'and');
        $this->sql = $this->replaceSqlLabel($this->sql, 'where', $parseWhere);
        return $this;
    }

    private function parseWhereStyleData($where, $andOr = 'and')
    {
        $clauses = [];
        foreach ($where as $row) {
            $expr = false;
            if (isset($row[3]) && '' != $row[3]) {
                $expr = $row[3];
            }
            list($column, $condition, $value) = $row;
            $condition = strtolower(trim($condition));
            $column = trim($column);
            if ('like' === $condition && '%%%' === trim($value)) {
                //todo throw 'sql like can not contain %%%'
            }
            if (false !== $expr || '' != $expr) {
                $clauses[] = $this->formatColumn($column) . ' ' . $condition . ' ' . $expr . ' ';
                continue;
            }
            if ('in' == $condition || 'not in' == $condition) {
                $clauses[] = $this->parseWhereIn($column, $condition, $value);
                continue;
            }
            $clauses[] = $this->formatColumn($column) . ' ' . $condition . $this->formatValue($value);
        }
        return implode(" $andOr ", $clauses);
    }

    private function parseWhereIn($column, $condition, $value)
    {
        $value = is_string($value) ? explode(',', $value) : $value;
        if (!is_array($value) || [] == $value) {
            //todo throw 'sql where条件中in为空'
        }
        $clause = $this->formatColumn($column) . ' ' . $condition . ' (';
        $tmp = [];
        foreach ($value as $v) {
            $tmp[] = $this->formatValue($v);
        }
        $clause .= implode(',', $tmp) . ') ';
        return $clause;
    }

    private function parseAnds($data)
    {
        for ($i = 0; $i < 20; $i++) {
            $andLabel = $i == 0 ? "and" : "and" . $i;
            if (!isset($data[$andLabel])) {
                break;
            }
            $this->parseAnd($data[$andLabel], $andLabel);
        }
        $this->sql = preg_replace('/#and\d#/i', '', $this->sql);
        return $this;
    }

    private function parseAnd($andData, $andLabel)
    {
        if (!is_array($andData) || [] == $andData) {
            $this->sql = $this->replaceSqlLabel($this->sql, 'and', '');
            return $this;
        }
        $replace = $this->parseWhereStyleData($andData, 'and');
        $this->sql = $this->replaceSqlLabel($this->sql, $andLabel, $replace);
        return $this;
    }

    private function parseOr($data)
    {
        $or = isset($data['or']) ? $data['or'] : [];
        if (!is_array($or) || [] == $or) {
            $this->sql = $this->replaceSqlLabel($this->sql, 'or', '');
            return $this;
        }
        $replace = $this->parseWhereStyleData($or, 'or');
        $this->sql = $this->replaceSqlLabel($this->sql, 'or', trim($replace, ' or'));
        return $this;
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
        $this->sql = $this->replaceSqlLabel($this->sql, 'order', $order);
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

        $this->sql = $this->replaceSqlLabel($this->sql, 'group', $group);
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
        $this->sql = $this->replaceSqlLabel($this->sql, 'limit', $limit);
        return $this;
    }

    private function replaceSqlLabel($sql, $label, $string)
    {
        return str_replace('#' . strtoUpper($label) . '#', $string, $sql);
    }

    private function addSqlLint($options)
    {
        $this->sqlMap = array_merge($this->sqlMap, $options);

        if (isset($this->sqlMap['use_master']) && $this->sqlMap['use_master']) {
            $this->sql = "/*master*/" . $this->sql;
        }
        return $this;
    }

}