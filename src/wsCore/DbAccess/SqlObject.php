<?php
namespace wsCore\DbAccess;

class SqlObject
{
    // public variables to represent sql statement.
    /** @var string           name of database table    */
    public $table;

    /** @var string           name of id (primary key)  */
    public $id_name = 'id';

    /** @var array            join for table            */
    public $join = array();

    /** @var string|array     columns to select in array or string     */
    public $columns;

    /** @var array            values for insert/update in array        */
    public $values = array();

    /** @var array            sql functions for insert/update          */
    public $functions = array();

    /** @var array            data to insert/update. from $values and $functions */
    public $rowData   = array();

    /** @var string */
    public $order;

    /** @var array|string */
    public $where;

    /** @var string */
    public $group;

    /** @var string */
    public $having;

    /** @var string */
    public $misc;

    /** @var bool|int */
    public $limit = FALSE;

    /** @var int */
    public $offset = 0;

    /** @var bool */
    public $distinct = FALSE;

    /** @var bool */
    public $forUpdate = FALSE;

}