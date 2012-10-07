<?php
namespace wsCore\DbAccess;

class Query
{
    // PdObject for executing and fetching result from DB.

    /** @var PdObject                   PDO object           */
    protected $pdoObj  = NULL;

    /** @var \PdoStatement               PDO statement obj   */
    protected $pdoStmt = NULL;

    // variables to build SQL statement.

    /** @var null|\wsCore\DbAccess\SqlObject                 */
    protected $sqlObj = NULL;

    /** @var string   SQL Statement created by this class    */
    protected $sql = '';

    /** @var string           name of database table         */
    protected $table;
    
    /** @var string           name of id (primary key)       */
    protected $id_name = 'id';

    /** @var array            stores data types of columns   */
    var $col_data_types = array();

    /** @var null             prepare/quote? null default    */
    public $prepQuoteUseType = NULL;

    /** @var string           default prepare/quote          */
    public static $pqDefault = 'prepare';

    /** @var SqlObject   SqlObject class    */
    public $sqlObject = '\wsCore\DbAccess\SqlObject';
    // +----------------------------------------------------------------------+
    //  Construction and Managing Dba Object.
    // +----------------------------------------------------------------------+
    /**
     * @param PdObject $pdoObj
     * @DimInjection  Get   \wsCore\DbAccess\PdObject
     */
    public function __construct( $pdoObj=NULL ) {
        $this->pdoObj = $pdoObj;
        $this->clear();
    }

    /**
     * clear returns brand new Sql object, instead of using
     * the same object and reset all variables.
     *
     * @return Query
     */
    public function clear() {
        $class = $this->sqlObject;
        $this->sqlObj = new $class( $this->pdoObj );
        $this->sqlObj->prepQuoteUseType = ( $this->prepQuoteUseType ) ?: static::$pqDefault;
        $this->sqlObj->col_data_types = $this->col_data_types;
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  executing with PdObject
    // +----------------------------------------------------------------------+
    /**
     * @param $mode
     * @param null $class
     * @return Query
     */
    public function setFetchMode( $mode, $class=NULL ) {
        $this->pdoObj->setFetchMode( $mode, $class );
        return $this;
    }

    /**
     * @return string
     */
    public function lastId() {
        return $this->pdoObj->lastId();
    }

    /**
     * @param string $table
     * @return Query
     */
    public function lockTable( $table=NULL ) {
        $table = ( $table )?: $this->table;
        $this->pdoObj->lockTable( $table );
        return $this;
    }

    /**
     * get driver name, such as mysql, sqlite, pgsql.
     * @return string
     */
    public function getDriverName() {
        return $this->pdoObj->getDriverName();
    }

    /**
     * executes SQL statement.
     *
     * @throws \RuntimeException
     * @return Query
     */
    public function exec() {
        $this->execSQL( $this->sql, $this->sqlObj->prepared_values, $this->sqlObj->prepared_types );
        return $this;
    }

    /**
     * executes SQL statement. mostly for backward compatibility.
     *
     * @param null  $sql
     * @param array $prepared
     * @param array $dataType
     * @throws \RuntimeException
     * @return Query
     */
    public function execSQL( $sql=NULL, $prepared=array(), $dataType=array() ) {
        if( !$this->pdoObj ) throw new \RuntimeException( 'Pdo Object not set.' );
        $this->pdoStmt = $this->pdoObj->exec( $sql, $prepared, $dataType );
        return $this;
    }
    public function execPrepare( $sql ) {
        $this->pdoObj->execPrepare( $sql );
        return $this;
    }
    public function execExecute( $prepared, $dataType=array() ) {
        $this->pdoObj->execExecute( $prepared, $dataType );
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  Getting result from PdoStatement.
    // +----------------------------------------------------------------------+
    /**
     * @return int|null
     */
    public function numRows() {
        if( is_numeric( $this->pdoStmt ) ) {
            return $this->pdoStmt;
        }
        return $this->pdoStmt->rowCount();
    }

    /**
     * @return int|null
     */
    public function fetchNumRow() {
        return $this->numRows();
    }

    /**
     * @return array
     */
    public function fetchAll() {
        if( is_object( $this->pdoStmt ) ) {
            return $this->pdoStmt->fetchAll();
        }
        return array();
    }

    /**
     * @param int $row
     * @throws \RuntimeException
     * @return array|mixed
     */
    public function fetchRow( $row=0 ) {
        if( is_object( $this->pdoStmt ) ) {
            if( $row > 0 ) {
                $driver = $this->getDriverName();
                if( $driver == 'mysql' || $driver == 'sqlite' ) {
                    throw new \RuntimeException( "Cannot fetch with offset for ".$driver );
                }
            }
            return $this->pdoStmt->fetch( null, \PDO::FETCH_ORI_ABS, $row );
        }
        return array();
    }
    // +----------------------------------------------------------------------+
    //  Quoting and Preparing Values for Prepared Statement.
    // +----------------------------------------------------------------------+
    /**
     * @param string $val
     * @return mixed
     */
    public function p( $val ) {
        $this->sqlObj->prepare( $val );
        return $val;
    }

    /**
     * @param string $val
     * @return string
     */
    public function q( $val ) {
        $this->sqlObj->quote( $val );
        return $val;
    }
    // +----------------------------------------------------------------------+
    //  Setting string, array, and data to build SQL statement.
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @param string $id_name
     * @return \wsCore\DbAccess\Query
     */
    public function table( $table, $id_name='id' ) {
        $this->clear();
        $this->table = $this->sqlObj->table = $table;
        $this->id_name = $this->sqlObj->id_name = $id_name;
        return $this;
    }

    /**
     * @param string|array $column
     * @return Query
     */
    public function column( $column ) {
        $this->sqlObj->columns = $column;
        return $this;
    }

    /**
     * set values for INSERT or UPDATE.
     * @param array $values
     * @return Query
     */
    public function values( $values ) {
        $this->sqlObj->values = $values;
        return $this;
    }

    /**
     * set SQL functions for INSERT or UPDATE. The functions are not 'prepared'.
     * TODO: find better name than functions??? how about rawValue?
     * @param $func
     * @return Query
     */
    public function functions( $func ) {
        $this->sqlObj->functions = $func;
        return $this;
    }
    public function order( $order ) {
        $this->sqlObj->order = $order;
        return $this;
    }
    public function group( $group ) {
        $this->sqlObj->group = $group;
        return $this;
    }
    public function misc( $misc ) {
        $this->sqlObj->misc = $misc;
        return $this;
    }
    public function limit( $limit ) {
        $this->sqlObj->limit  = ( $limit  ) ? $limit : FALSE;
        return $this;
    }
    public function offset( $offset ) {
        $this->sqlObj->offset = ( is_numeric( $offset ) ) ? $offset: 0;
        return $this;
    }

    /**
     * creates SELECT DISTINCT statement.
     * @return Query
     */
    public function distinct(){
        $this->sqlObj->distinct = TRUE;
        return $this;
    }

    /**
     * creates SELECT for UPDATE statement.
     * @return Query
     */
    public function forUpdate() {
        $this->sqlObj->forUpdate = TRUE;
        return $this;
    }

    /**
     * Building JOIN clause...
     * TODO: should move this to SqlBuilder.
     *
     * @param $table
     * @param $join
     * @param null $by
     * @param null $columns
     * @return Query
     */
    public function join( $table, $join, $by=NULL, $columns=NULL ) {
        $this->sqlObj->join[] = "{$join} {$table}" . ($by)? " {$by}( {$columns} )": '';
        return $this;
    }
    public function joinUsing( $table, $columns ) {
        return $this->join( $table, 'JOIN', 'USING', $columns );
    }
    public function joinLeftUsing( $table, $columns ) {
        return $this->join( $table, 'LEFT JOIN', 'ON', $columns );
    }
    public function joinOn( $table, $columns ) {
        return $this->join( $table, 'JOIN', 'USING', $columns );
    }
    public function joinLeftOn( $table, $columns ) {
        return $this->join( $table, 'LEFT JOIN', 'ON', $columns );
    }
    // +----------------------------------------------------------------------+
    //  Building WHERE clause.
    // +----------------------------------------------------------------------+
    /**
     * set where statement with values properly prepared/quoted. 
     * 
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param null|string|bool   $type
     * @return Query
     */
    public function where( $col, $val, $rel='=', $type=NULL ) {
        $this->sqlObj->where( $col, $val, $rel, $type );
        return $this;
    }

    /**
     * set where statement as is. 
     * 
     * @param        $col
     * @param        $val
     * @param string $rel
     * @return Query
     */
    public function whereRaw( $col, $val, $rel='=' ) {
        $this->sqlObj->whereRaw( $col, $val, $rel );
        return $this;
    }

    /**
     * sets OR operation for the last where statement data. 
     * 
     * @return Query
     */
    public function or_() {
        $this->sqlObj->modRaw( array( 'op' => 'OR' ) );
        return $this;
    }
    public function w( $col ) {
        $this->sqlObj->col( $col );
        return $this;
    }
    public function mod( $val, $rel, $type=NULL ) {
        $mod = array( 'val' => $val, 'rel' => $rel );
        $this->sqlObj->mod( $mod, $type );
        return $this;
    }
    public function modRaw( $val, $rel ) {
        $mod = array( 'val' => $val, 'rel' => $rel );
        $this->sqlObj->modRaw( $mod );
        return $this;
    }
    public function eq( $val, $type=NULL ) {
        return $this->mod( $val, '=', $type );
    }
    public function ne( $val, $type=NULL ) {
        return $this->mod( $val, '!=', $type );
    }
    public function lt( $val, $type=NULL ) {
        return $this->mod( $val, '<', $type );
    }
    public function le( $val, $type=NULL ) {
        return $this->mod( $val, '<=', $type );
    }
    public function gt( $val, $type=NULL ) {
        return $this->mod( $val, '>', $type );
    }
    public function ge( $val, $type=NULL ) {
        return $this->mod( $val, '>=', $type );
    }
    public function in( $val, $type=NULL ) {
        return $this->mod( $val, 'IN', $type );
    }
    public function notIn( $val, $type=NULL ) {
        return $this->mod( $val, 'NOT IN', $type );
    }
    public function between( $val, $type=NULL ) {
        return $this->mod( $val, 'BETWEEN', $type );
    }
    public function isNull() {
        return $this->modRaw( NULL, 'IS NULL' );
    }
    public function notNull() {
        return $this->modRaw( NULL, 'IS NOT NULL' );
    }
    public function like( $val, $type=NULL ) {
        return $this->mod( $val, 'LIKE', $type );
    }
    public function contain( $val, $type=NULL ) {
        return $this->mod( "%{$val}%", 'LIKE', $type );
    }
    public function startWith( $val, $type=NULL ) {
        return $this->mod( $val.'%', 'LIKE', $type );
    }
    public function endWith( $val, $type=NULL ) {
        return $this->mod( '%'.$val, 'LIKE', $type );
    }
    /**
     * sets where. replaces where data as is.
     * @param string $where
     * @return Query
     */
    public function setWhere( $where ) {
        $this->sqlObj->where = $where;
        return $this;
    }

    /**
     * @param string $where
     * @return Query
     */
    public function addWhere( $where ) {
        return $this->whereRaw( $where, '', '' );
    }

    /**
     * @return Query
     */
    public function clearWhere() {
        $this->sqlObj->where = array();
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  constructing and executing SQL statement.
    // +----------------------------------------------------------------------+
    /**
     * @param array $values
     * @return Query
     */
    public function update( $values ) {
        return $this->values( $values )->makeUpdate()->exec();
    }

    /**
     * @param array $values
     * @return Query
     */
    public function insert( $values ) {
        return $this->values( $values )->makeInsert()->exec();
    }

    /**
     * @param array|null $column
     * @return Query
     */
    public function select( $column=NULL ) {
        if( $column ) $this->column( $column );
        return $this->makeSelect()->exec();
    }

    /**
     * @return string
     */
    public function count() {
        return $this->makeCount()->exec()->pdoStmt->fetchColumn(0);
    }

    /**
     * makes SQL statement. $types are:
     * INSERT, UPDATE, DELETE, COUNT, SELECT.
     * @param $type
     * @return Query
     */
    public function makeSQL( $type )
    {
        $type = 'make' . strtoupper( $type );
        return $this->$type();
    }
    public function makeSelect() {
        $this->sql = SqlBuilder::makeSelect( $this->sqlObj );
        return $this;
    }
    public function makeCount() {
        $this->sql = SqlBuilder::makeCount( $this->sqlObj );
        return $this;
    }
    public function makeDelete() {
        $this->sql = SqlBuilder::makeDelete( $this->sqlObj );
        return $this;
    }
    public function makeInsert() {
        $this->processValues();
        $this->sql = SqlBuilder::makeInsert( $this->sqlObj );
        return $this;
    }
    public function makeUpdate() {
        $this->processValues();
        $this->sql = SqlBuilder::makeUpdate( $this->sqlObj );
        return $this;
    }

    /**
     * prepares value for prepared statement. if value is NULL,
     * it will not be treated as prepared value, instead it is
     * set to SQL's NULL value.
     *
     * @return Query
     */
    public function processValues()
    {
        $this->sqlObj->processValues();
        return $this;
    }
    // +----------------------------------------------------------------------+
}