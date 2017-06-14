<?php
namespace Zan\Framework\Store\Database\Sql;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Store\Database\Sql\SqlParser;
use Zan\Framework\Store\Database\Sql\SqlBuilder;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Store\Database\Sql\Exception\SqlMapCanNotFindException;

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
        $sqlMap = $this->builder($sqlMap, $data, $options);
        return $sqlMap;
    }

    private function builder($sqlMap, $data, $options)
    {
        return (new SqlBuilder())->setSqlMap($sqlMap)->builder($data, $options)->getSqlMap();
    }

    private function getSqlMapBySid($sid)
    {
        $sidData = $this->parseSid($sid);
        $key = $sidData['key'];
        $filePath = $sidData['file_path'];
        if (!isset($this->sqlMaps[$filePath]) || [] == $this->sqlMaps[$filePath]) {
            throw new SqlMapCanNotFindException('no such sql map file path :'.$sid);
        }
        $sqlMap = $this->sqlMaps[$filePath];
        if (!isset($sqlMap[$key]) || [] == $sqlMap[$key]) {
            throw new SqlMapCanNotFindException('no such sql map key :'. $sid);
        }
        return $sqlMap[$key];
    }

    private function parseSid($sid)
    {
        $pos = strrpos($sid, '.');
        if (false === $pos) {
            throw new SqlMapCanNotFindException('no such sql id');
        }

        $filePath = substr($sid, 0, $pos);
        $base = explode('.', $filePath);

        return [
            'file_path' => $filePath,
            'base'      => $base,
            'key'       => substr($sid, $pos + 1),
        ];
    }
}



