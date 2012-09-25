<?php
namespace wsCore\DbAccess;

/*
which is more cool?

$sql->table( 'table' )->where( 'id', 10 )->select();

$sql->table( 'table' )->w( 'id' )->eq( 10 )->select();
$sql->table( 'table' )->w( 'id' )->le( 10 )->select();
$sql->table( 'table' )->w( 'name' )->startWith( 'WScore' )->select();
$sql->table( 'table' )->w( 'name' )->isNull()->select();


*/
class Sql
{
    // public variables to represent sql statement.
    /** @var string           name of database table    */
    var $table;
    
    /** @var string           name of id (primary key)  */
    var $id_name = 'id';

    public $sqlObj = NULL;
    
    /** @var array    stores data types of columns           */
    var $col_data_types = array();
    
    /** @var string   SQL Statement created by this class    */
    var $sql = '';

    /** @var Dba      DataBase Access object                 */
    private $dba;

    public $prepQuoteUseType = NULL;
    public static $pqDefault = 'prepare';
    // +----------------------------------------------------------------------+
    //  Construction and Managing Dba Object.
    // +----------------------------------------------------------------------+
    /**
     * @param Dba $dba
     */
    public function __construct( $dba=NULL ) {
        $this->dba = ( $dba ) ?: NULL;
        $this->sqlObj = new SqlObject();
    }

    public function setDba( $dba ) {
        $this->dba = $dba;
    }
    /**
     * clear returns brand new Sql object, instead of using
     * the same object and reset all variables.
     *
     * @return Sql
     */
    public function clear() {
        $this->sqlObj = new SqlObject();
        $this->sqlObj->pdoObj = $this->dba->pdoObj;
        $this->sqlObj->prepQuoteUseType = ( $this->prepQuoteUseType ) ?: static::$pqDefault;
        $this->sqlObj->col_data_types = $this->col_data_types;
        return $this;
    }

    /**
     * executes SQL statement.
     *
     * @throws \RuntimeException
     * @return Dba
     */
    public function exec() {
        return $this->dba->execSQL( $this->sql, $this->sqlObj->prepared_values, $this->sqlObj->prepared_types );
    }

    /**
     * executes SQL statement. mostly for backward compatibility.
     *
     * @param null  $sql
     * @param array $prepared
     * @param array $dataType
     * @throws \RuntimeException
     * @return Dba
     */
    public function execSQL( $sql=NULL, $prepared=array(), $dataType=array() ) {
        if( !$this->dba ) throw new \RuntimeException( 'DbAccess object not set to perform this method.' );
        $this->dba->execSQL( $sql, $prepared, $dataType );
        return $this->dba;
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
     * @return Sql
     */
    public function table( $table, $id_name='id' ) {
        $this->table = $this->sqlObj->table = $table;
        $this->id_name = $this->sqlObj->id_name = $id_name;
        return $this;
    }

    /**
     * @param string|array $column
     * @return Sql
     */
    public function column( $column ) {
        $this->sqlObj->columns = $column;
        return $this;
    }

    /**
     * set values for INSERT or UPDATE.
     * @param array $values
     * @return Sql
     */
    public function values( $values ) {
        $this->sqlObj->values = $values;
        return $this;
    }

    /**
     * set SQL functions for INSERT or UPDATE. The functions are not 'prepared'.
     * TODO: find better name than functions??? how about rawValue?
     * @param $func
     * @return Sql
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
     * @return Sql
     */
    public function distinct(){
        $this->sqlObj->distinct = TRUE;
        return $this;
    }

    /**
     * creates SELECT for UPDATE statement.
     * @return Sql
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
     * @return Sql
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
     * @return Sql
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
     * @return Sql
     */
    public function whereRaw( $col, $val, $rel='=' ) {
        $this->sqlObj->whereRaw( $col, $val, $rel );
        return $this;
    }

    /**
     * sets OR operation for the last where statement data. 
     * 
     * @return Sql
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
        return $this->modRaw( NULL, 'NOT NULL' );
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
     * @return Sql
     */
    public function setWhere( $where ) {
        $this->sqlObj->where = $where;
        return $this;
    }

    /**
     * @param string $where
     * @return Sql
     */
    public function addWhere( $where ) {
        return $this->whereRaw( $where, '', '' );
    }

    /**
     * @return Sql
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
     * @return \PdoStatement
     */
    public function update( $values ) {
        return $this->values( $values )
            ->makeSQL( 'UPDATE' )
            ->exec();
    }

    /**
     * @param array $values
     * @return \PdoStatement
     */
    public function insert( $values ) {
        return $this->values( $values )
            ->makeSQL( 'INSERT' )
            ->exec();
    }

    /**
     * @param array|null $column
     * @return \wsCore\DbAccess\Dba
     */
    public function select( $column=NULL ) {
        if( $column ) $this->column( $column );
        return $this->makeSQL( 'SELECT' )
            ->exec();
    }

    /**
     * @return string
     */
    public function count() {
        return $this->makeSQL( 'COUNT' )
            ->exec()
            ->stmt()->fetchColumn(0);
    }

    /**
     * makes SQL statement. $types are:
     * INSERT, UPDATE, DELETE, COUNT, SELECT.
     * @param $type
     * @return Sql
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
     * @return Sql
     */
    public function processValues()
    {
        $this->sqlObj->processValues();
        return $this;
    }
    // +----------------------------------------------------------------------+
}