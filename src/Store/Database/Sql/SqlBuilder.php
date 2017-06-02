<?php
namespace Zan\Framework\Store\Database\Sql;

use Zan\Framework\Store\Database\Sql\Exception\SqlBuilderException;

class SqlBuilder
{
    private $sqlMap;

    public function setSqlMap($sqlMap)
    {
        $this->sqlMap = $sqlMap;
        return $this;
    }

    public function getSqlMap()
    {
        return $this->sqlMap;
    }

    public function builder($data, $options)
    {
        switch ($this->sqlMap['sql_type']) {
            case 'select' :
                $this->select($data);
                break;
            case 'insert' :
                $this->insert($data);
                break;
            case 'update' :
                $this->update($data);
                break;
            case 'delete' :
                $this->delete($data);
                break;
        }
        $this->addSqlLint($options)->formatSql();
        return $this;
    }

    private function select($data)
    {
        $this->checkRequire($data);
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
            throw new SqlBuilderException('what field do you want count?');
        }
        $count = 'count(' . $data['count'] . ')';
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'count', $count);
        $this->sqlMap['count_alias'] = $count;
        return $this;
    }

    private function insert($data)
    {
        $this->parseVars($data);
        if (isset($data['inserts'])) {
            return $this->batchInserts($data);
        }
        $this->checkInsertRequire($data);
        $insert = isset($data['insert']) ? $data['insert'] : [];
        if (!is_array($insert) || count($insert) == 0) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'insert', '');
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
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'insert', $replace);
        return $this;
    }

    private function batchInserts($data)
    {
        $inserts = isset($data['inserts']) ? $data['inserts'] : [];
        if (!is_array($inserts) || count($inserts) == 0) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'inserts', '');
            return $this;
        }
        foreach ($data['inserts'] as $insert) {
            $this->checkInsertRequire(['insert' => $insert]);
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
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'inserts', $replace);
        return $this;
    }

    private function update($data)
    {
        $this->parseUpdateData($data);
        $this->checkRequire($data);
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
        $this->checkRequire($data);
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
        $value = Validator::realEscape($value);
        return is_int($value) ? $value : "'" . $value . "'";
    }

    private function checkRequire($data)
    {
        if (!isset($data['where']) && !isset($data['and']) && !isset($data['or'])) {
            return true;
        }
        $where = isset($data['where']) ? $data['where'] : [];
        $where = isset($data['and']) ? array_merge($where, $data['and']) : $where;
        $where = isset($data['or']) ? array_merge($where, $data['or']) : $where;

        $requireMap = [];
        $limitMap = [];
        if (is_array($this->sqlMap['require']) && [] != $this->sqlMap['require']) {
            $requireMap = array_flip($this->sqlMap['require']);
        }
        if (is_array($this->sqlMap['limit']) && [] != $this->sqlMap['limit']) {
            $limitMap = array_flip($this->sqlMap['limit']);
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
                    throw new SqlBuilderException('sql map limit error, your insert column not in limit map:'.$col);
                }
            }
        }
        if (count($requireMap) > 0) {
            throw new SqlBuilderException('sql map require error, require map need your insert must have the columns:'.implode(', ', array_keys($requireMap)));
        }
        return true;
    }

    private function checkInsertRequire($data)
    {
        if (!isset($data['insert'])) {
            return true;
        }
        $insert = $data['insert'];
        $requireMap = [];
        $limitMap = [];
        if (is_array($this->sqlMap['require']) && [] != $this->sqlMap['require']) {
            $requireMap = array_flip($this->sqlMap['require']);
        }
        if (is_array($this->sqlMap['limit']) && [] != $this->sqlMap['limit']) {
            $limitMap = array_flip($this->sqlMap['limit']);
        }

        if ([] == $requireMap && [] == $limitMap) {
            return true;
        }

        foreach($insert as $column => $value) {
            if (count($requireMap) > 0) {
                if (isset($requireMap[$column])) {
                    unset($requireMap[$column]);
                }
            }
            if (count($limitMap) > 0) {
                if (!isset($limitMap[$column])) {
                    throw new SqlBuilderException('sql map limit error, your insert column not in limit map:'.$column);
                }
            }
        }
        if (count($requireMap) > 0) {
            throw new SqlBuilderException('sql map require error, require map need your insert must have the columns:'.implode(', ', array_keys($requireMap)));
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
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'column', $column);
        return $this;
    }

    private function parseUpdateData($data)
    {
        if (!$data || !isset($data['data']) || [] == $data['data']) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'data', '');
            return $this;
        }

        $update = $data['data'];
        if (!isset($update[0])) {
            $tmp = [];
            foreach ($update as $column => $value) {
                $tmp[] = [$column, Validator::realEscape($value)];
            }
            $update = $tmp;
        }
        if (count($update) == 0) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'data', '');
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
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'data', $replace);
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
        $this->sqlMap['sql'] = str_replace($firstLabels, $replaces, $this->sqlMap['sql']);
        $this->sqlMap['sql'] = str_replace($secLabels, $replaces, $this->sqlMap['sql']);
        return $this;
    }



    private function parseWhere($data)
    {
        $where = isset($data['where']) ? $data['where'] : [];
        if (!is_array($where) || [] == $where) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'where', '');
            return $this;
        }
        $parseWhere = $this->parseWhereStyleData($where, 'and');
        preg_match('/where([^#]*)#where#/i', $this->sqlMap['sql'], $match);
        if (isset($match[1])) {
            if ('' != trim($match[1])) {
                $parseWhere = ' and ' . $parseWhere;
            }
        }
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'where', $parseWhere);
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
                throw new SqlBuilderException('sql like can not contain %%%');
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
            throw new SqlBuilderException('sql where条件中in为空');
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
        $this->sqlMap['sql'] = preg_replace('/#and\d#/i', '', $this->sqlMap['sql']);
        return $this;
    }

    private function parseAnd($andData, $andLabel)
    {
        if (!is_array($andData) || [] == $andData) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'and', '');
            return $this;
        }
        $replace = $this->parseWhereStyleData($andData, 'and');
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], $andLabel, $replace);
        return $this;
    }

    private function parseOr($data)
    {
        $or = isset($data['or']) ? $data['or'] : [];
        if (!is_array($or) || [] == $or) {
            $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'or', '');
            return $this;
        }
        $replace = $this->parseWhereStyleData($or, 'or');
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'or', trim($replace, ' or'));
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
        $this->sqlMap['limit'] = '';
        if(isset($data['limit']) && '' !== $data['limit']) {
            $this->sqlMap['limit'] = trim($data['limit']);
        }
        if ('' != $this->sqlMap['limit']) {
            $this->sqlMap['limit'] = ' limit ' . $this->sqlMap['limit'] . ' ';
        }
        $this->sqlMap['sql'] = $this->replaceSqlLabel($this->sqlMap['sql'], 'limit', $this->sqlMap['limit']);
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
            $this->sqlMap['sql'] = "/*master*/" . $this->sqlMap['sql'];
        }
        return $this;
    }

    private function formatSql()
    {
        $sql = trim($this->sqlMap['sql']);
        $sql = str_replace("\n", NULL, $sql);
        $sql = str_replace("\r", NULL, $sql);
        $this->sqlMap['sql'] = $sql;
        return $this;
    }
}