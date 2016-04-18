<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/12
 * Time: 下午5:05
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
     * @return mixed(base on ResultType)
     */
    public function format()
    {
        switch ($this->resultType) {
            case ResultTypeInterface::INSERT :
                $result = $this->insert();
                break;
            case ResultTypeInterface::UPDATE :
                $result = $this->update();
                break;
            case ResultTypeInterface::DELETE :
                $result = $this->delete();
                break;
            case ResultTypeInterface::BATCH :
                $result = $this->batch();
                break;
            case ResultTypeInterface::ROW :
                $result = $this->row();
                break;
            case ResultTypeInterface::RAW :
                $result = $this->raw();
                break;
            case ResultTypeInterface::SELECT :
                $result = $this->select();
                break;
            case ResultTypeInterface::COUNT :
                $result = $this->count();
                break;
            case ResultTypeInterface::LAST_INSERT_ID :
                $result = $this->lastInsertId();
                break;
            case ResultTypeInterface::AFFECTED_ROWS :
                $result = $this->affectedRows();
                break;
            default :
                $result = $this->raw();
                break;
        }
        return $result;
    }

    private function select()
    {
        $rows = $this->dbResult->fetchRows();
        return null == $rows || [] == $rows ? [] : $rows;
    }

    private function count()
    {
        $rows = $this->dbResult->fetchRows();
        return !isset($rows[0]['count_sql_rows']) ? 0 : $rows[0]['count_sql_rows'];
    }

    private function insert()
    {
        return $this->dbResult->fetchRows();
    }

    private function lastInsertId()
    {
        return $this->dbResult->getLastInsertId();
    }

    private function update()
    {
        return $this->dbResult->fetchRows();
    }

    private function delete()
    {
        return $this->dbResult->fetchRows();
    }

    private function affectedRows()
    {
        return $this->dbResult->getAffectedRows();
    }

    private function batch()
    {
        return $this->dbResult->fetchRows();
    }

    private function row()
    {
        $rows = $this->dbResult->fetchRows();
        return isset($rows[0]) && [] != $rows[0] ? $rows[0] : null;
    }

    private function raw()
    {
        return $this->dbResult->fetchRows();
    }
}