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
//        $this->sqlMap = $this->getSqlMapBySid($sid);
//        $type = strtolower($this->getSqlType());
//        $this->sqlMap['sql_type'] = $this->getSqlType();
    }

    private function parse($sqlMap)
    {
        return (new SqlParser())->setSqlMap($sqlMap)->parse()->getSqlMap();
    }

    private function builder($sql, $data, $require, $limit)
    {
        return (new SqlBuilder())->setSql($sql)->builder($data, $require, $limit)->getSql();
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
        if ([] != $sqlMap && isset($sqlMap[$mapKey])) {
            return $sqlMap[$mapKey];
        }

        $sqlMap = $this->getSqlFile($filePath);
        if (!$sqlMap || !isset($sqlMap[$mapKey])) {
            //todo throw 'no such sql: ' . $sid
        }
        $this->sqlMaps[$filePath] = $this->parse($sqlMap);
        return $this->sqlMaps[$filePath][$mapKey];
    }

    private function getSqlFile($filePath)
    {
        return require Path::getSqlPath() . $filePath . '.php';
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


    //判断该表是否需要分表
    private function splitTable($data)
    {
        //todo
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



