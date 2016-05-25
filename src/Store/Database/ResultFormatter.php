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
namespace Zan\Framework\Store\Database;
use Zan\Framework\Contract\Store\Database\ResultFormatterInterface;
use Zan\Framework\Contract\Store\Database\DbResultInterface;
use Zan\Framework\Contract\Store\Database\ResultTypeInterface;

class ResultFormatter implements ResultFormatterInterface
{
    private $dbResult;

    private $resultType;

    /**
     * ResultFormatterInterface constructor.
     * @param DbResultInterface $result
     * @param int $resultType
     */
    public function __construct(DbResultInterface $result, $resultType = ResultTypeInterface::RAW)
    {
        $this->dbResult = $result;
        $this->resultType = $resultType;
    }

    /**
     * @yield mixed(base on ResultType)
     */
    public function format()
    {
        switch ($this->resultType) {
            case ResultTypeInterface::INSERT :
                $result = (yield $this->insert());
                break;
            case ResultTypeInterface::UPDATE :
                $result = (yield $this->update());
                break;
            case ResultTypeInterface::DELETE :
                $result = (yield $this->delete());
                break;
            case ResultTypeInterface::BATCH :
                $result = (yield $this->batch());
                break;
            case ResultTypeInterface::ROW :
                $result = (yield $this->row());
                break;
            case ResultTypeInterface::RAW :
                $result = (yield $this->raw());
                break;
            case ResultTypeInterface::SELECT :
                $result = (yield $this->select());
                break;
            case ResultTypeInterface::COUNT :
                $result = (yield $this->count());
                break;
            case ResultTypeInterface::LAST_INSERT_ID :
                $result = (yield $this->lastInsertId());
                break;
            case ResultTypeInterface::AFFECTED_ROWS :
                $result = (yield $this->affectedRows());
                break;
            default :
                $result = (yield $this->raw());
                break;
        }
        yield $result;
    }

    private function select()
    {
        $rows = (yield $this->dbResult->fetchRows());
        yield null == $rows || [] == $rows ? [] : $rows;
    }

    private function count()
    {
        $rows = (yield $this->dbResult->fetchRows());
        yield !isset($rows[0]['count_sql_rows']) ? 0 : (int)$rows[0]['count_sql_rows'];
    }

    private function insert()
    {
        yield $this->dbResult->fetchRows();
    }

    private function lastInsertId()
    {
        yield $this->dbResult->getLastInsertId();
    }

    private function update()
    {
        yield $this->dbResult->fetchRows();
    }

    private function delete()
    {
        yield $this->dbResult->fetchRows();
    }

    private function affectedRows()
    {
        yield $this->dbResult->getAffectedRows();
    }

    private function batch()
    {
        yield $this->dbResult->fetchRows();
    }

    private function row()
    {
        $rows = (yield $this->dbResult->fetchRows());
        yield isset($rows[0]) && [] != $rows[0] ? $rows[0] : null;
    }

    private function raw()
    {
        yield $this->dbResult->fetchRows();
    }
}