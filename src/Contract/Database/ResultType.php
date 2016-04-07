<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/6
 * Time: 10:39
 */

namespace Zan\Framework\Contract\Database;


interface ResultType
{
    /**
     * return the query return
     */
    const RAW               = 0;

    /**
     * return one row : [<k=>v>]
     */
    const ROW               = 1;

    /**
     * return Table : [ [<k=>v>] ]
     */
    const SELECT            = 2;

    /**
     * return bool
     */
    const UPDATE            = 3;

    /**
     * return bool
     */
    const DELETE            = 4;

    /**
     * return bool 
     */
    const INSERT            = 5;

    /**
     * return bool
     */
    const BATCH             = 6;

    /**
     * return int
     */
    const COUNT             = 7;

    /**
     * return last_insert_id : int | bigint
     */
    const LAST_INSERT_ID    = 8;

    /**
     * return affeted_row : int
     */
    const AFFECTED_ROWS     = 9;
}