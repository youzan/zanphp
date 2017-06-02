<?php
namespace Zan\Framework\Store\Database\Sql;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Core\ConfigLoader;
use Zan\Framework\Store\Database\Sql\SqlMap;

class SqlMapInitiator
{
    use Singleton;

    public function init()
    {
        $sqlPath = Path::getSqlPath();
        if (!is_dir($sqlPath)) {
            return false;
        }
        $sqlMaps = ConfigLoader::getInstance()->loadDistinguishBetweenFolderAndFile($sqlPath);
        if (null == $sqlMaps || [] == $sqlMaps) {
            return false;
        }
        foreach ($sqlMaps as $key => $sqlMap) {
            $sqlMaps[$key] = (new SqlParser())->setSqlMap($sqlMap)->parse()->getSqlMap();
        }
        SqlMap::getInstance()->setSqlMaps($sqlMaps);
        return true;
    }
}