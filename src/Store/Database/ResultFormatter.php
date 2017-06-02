<?php
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
        yield $this->dbResult->getCountRows();
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