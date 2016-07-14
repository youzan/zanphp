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