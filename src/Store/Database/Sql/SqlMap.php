<?php
namespace Zan\Framework\Store\Database\Sql;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Store\Database\Sql\SqlParser;
use Zan\Framework\Store\Database\Sql\SqlBuilder;
use Zan\Framework\Foundation\Core\Path;

class SqlMap
{
    use Singleton;

    private $sqlMaps = [];

    public function setSqlMaps($sqlMaps)
    {
        $this->sqlMaps = $sqlMaps;
    }

    public function getSql($sid, $data = [], $options = [])
    {
        $sqlMap = $this->getSqlMapBySid($sid);
        $sqlMap['sql'] = $this->builder($sqlMap['sql'], $data, $sqlMap['sql_type'], $sqlMap['require'], $sqlMap['limit']);
        return $sqlMap;
    }

    private function builder($sql, $data, $sqlType, $require, $limit)
    {
        return (new SqlBuilder())->setSql($sql)->builder($data, $sqlType, $require, $limit)->getSql();
    }

    private function getSqlMapBySid($sid)
    {
        $sidData = $this->parseSid($sid);
        $base = $sidData['base'];
        $mapKey = $sidData['key'];
        $sqlMap = [];
        foreach ($base as $route) {
            if ([] == $sqlMap && !isset($this->sqlMaps[$route])) {
                break;
            }
            $sqlMap = [] == $sqlMap ? $this->sqlMaps[$route] : $sqlMap[$route];
        }
        if (!isset($sqlMap[$mapKey]) || [] == $sqlMap[$mapKey]) {
            //todo throw no suck sql map
        }
        return $sqlMap[$mapKey];
    }

    private function parseSid($sid)
    {
        $pos = strrpos($sid, '.');
        if (false === $pos) {
            //todo throw 'no such sql id'
        }

        $filePath = substr($sid, 0, $pos);
        $base = explode('.', $filePath);
        $filePath = str_replace('.', '/', $filePath);

        return [
            'file_path' => $filePath,
            'base'      => $base,
            'key'       => substr($sid, $pos + 1),
        ];
    }
}



