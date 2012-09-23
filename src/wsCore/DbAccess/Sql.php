<?php
namespace wsCore\DbAccess;

class Sql
{
    // public variables to represent sql statement.
    /** @var string           name of database table    */
    var $table;
    
    /** @var string           name of id (primary key)  */
    var $id_name = 'id';

    /** @var array            join for table            */
    var $join = array();

    /** @var string|array     columns to select in array or string     */
    var $columns;

    /** @var array            values for insert/update in array        */
    var $values = array();

    /** @var array            sql functions for insert/update          */
    var $functions = array();

    /** @var array            data to insert/update. from $values and $functions */
    var $rowData   = array();

    /** @var string */
    var $order;

    /** @var array|string */
    var $where;

    /** @var string */
    var $group;

    /** @var string */
    var $having;

    /** @var string */
    var $misc;

    /** @var bool|int */
    var $limit = FALSE;

    /** @var int */
    var $offset = 0;

    /** @var bool */
    var $distinct = FALSE;

    /** @var bool */
    var $forUpdate = FALSE;

    /** @var array    stores prepared values and holder name */
    var $prepared_values = array();
    
    /** @var array    stores data types of place holders */
    var $prepared_types = array();

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
    }

    /**
     * clear returns brand new Sql object, instead of using
     * the same object and reset all variables.
     *
     * @return Sql
     */
    public function clear() {
        return new self( $this->dba );
    }

    /**
     * executes SQL statement.
     *
     * @throws \RuntimeException
     * @return Dba
     */
    public function exec() {
        if( !$this->dba ) throw new \RuntimeException( 'DbAccess object not set to perform this method.' );
        return $this->dba->execSQL( $this->sql, $this->prepared_values );
    }

    /**
     * executes SQL statement. mostly for backward compatibility.
     *
     * @param null  $sql
     * @param array $prepared
     * @throws \RuntimeException
     * @return \PdoStatement
     */
    public function execSQL( $sql=NULL, $prepared=array() ) {
        if( !$this->dba ) throw new \RuntimeException( 'DbAccess object not set to perform this method.' );
        $sql = ( $sql ) ?: $this->sql;
        $this->dba->execSQL( $sql, $prepared );
        return $this->dba->stmt();
    }
    // +----------------------------------------------------------------------+
    //  Quoting and Preparing Values for Prepared Statement.
    // +----------------------------------------------------------------------+
    /**
     * pre-process values with prepare or quote method.
     *
     * @param      $val
     * @param null $type    data type
     * @return Sql
     */
    public function prepOrQuote( &$val, $type=NULL )
    {
        $pqType = ( $this->prepQuoteUseType )?: static::$pqDefault;
        $this->$pqType( $val, $type );
        return $this;
    }

    /**
     * replaces value with holder for prepared statement. the value is kept
     * inside prepared_value array.
     *
     * @param string|array $val
     * @param null|int     $type    data type
     * @return Sql
     */
    public function prepare( &$val, $type=NULL )
    {
        if( is_array( $val ) ) {
            foreach( $val as &$v ) {
                $this->prepare( $v, $type );
            }
        }
        else {
            $holder = ':db_prep_' . count( $this->prepared_values );
            $this->prepared_values[ $holder ] = $val;
            $val = $holder;
            if( $type ) $this->prepared_types[ $holder ] = $type;
        }
        return $this;
    }

    /**
     * @param string|array $val
     * @param null|int     $type    data type
     * @return Sql
     */
    public function quote( &$val, $type=NULL )
    {
        if( is_array( $val ) ) {
            foreach( $val as &$v ) {
                $this->quote( $v, $type );
            }
        }
        elseif( isset( $this->dba ) ) {
            /** @var $pdo \Pdo */
            $pdo = $this->dba->pdo();
            $val = $pdo->quote( $val );
        }
        else {
            $val = addslashes( $val );
        }
        return $this;
    }

    /**
     * @param string $val
     * @return mixed
     */
    public function p( $val ) {
        $this->prepare( $val );
        return $val;
    }

    /**
     * @param string $val
     * @return string
     */
    public function q( $val ) {
        $this->quote( $val );
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
        $this->table = $table;
        $this->id_name = $id_name;
        return $this;
    }

    /**
     * @param string|array $column
     * @return Sql
     */
    public function column( $column ) {
        $this->columns = $column;
        return $this;
    }

    /**
     * set values for INSERT or UPDATE.
     * @param array $values
     * @return Sql
     */
    public function values( $values ) {
        $this->values = $values;
        return $this;
    }

    /**
     * set SQL functions for INSERT or UPDATE. The functions are not 'prepared'.
     * TODO: find better name than functions???
     * @param $func
     * @return Sql
     */
    public function functions( $func ) {
        $this->functions = $func;
        return $this;
    }
    public function order( $order ) {
        $this->order = $order;
        return $this;
    }
    public function group( $group ) {
        $this->group = $group;
        return $this;
    }
    public function misc( $misc ) {
        $this->misc = $misc;
        return $this;
    }
    public function limit( $limit ) {
        $this->limit  = ( $limit  ) ? $limit : FALSE;
        return $this;
    }
    public function offset( $offset ) {
        $this->offset = ( is_numeric( $offset ) ) ? $offset: 0;
        return $this;
    }

    /**
     * creates SELECT DISTINCT statement.
     * @return Sql
     */
    public function distinct(){
        $this->distinct = TRUE;
        return $this;
    }

    /**
     * creates SELECT for UPDATE statement.
     * @return Sql
     */
    public function forUpdate() {
        $this->forUpdate = TRUE;
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
        $this->join[] = "{$join} {$table}" . ($by)? " {$by}( {$columns} )": '';
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
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param string $op
     * @return Sql
     */
    public function where( $col, $val, $rel='=', $op='' ) {
        $this->prepOrQuote( $val );
        $where = array( 'col' => $col, 'val'=> $val, 'rel' => $rel, 'op' => $op );
        $this->where[] = $where;
        return $this;
    }

    /**
     * sets where. replaces where data as is.
     * @param string $where
     * @return Sql
     */
    public function setWhere( $where ) {
        $this->where = $where;
        return $this;
    }

    /**
     * @param string $where
     * @param string $op
     * @return Sql
     */
    public function addWhere( $where, $op='AND' ) {
        return $this->where( $where, '', '', $op );
    }

    /**
     * @return Sql
     */
    public function clearWhere() {
        $this->where = NULL;
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
        $type = strtoupper( $type );
        switch( $type ) {
            case 'INSERT':
                $this->processValues();
                $sql = SqlBuilder::makeInsert( $this );
                break;
            case 'UPDATE':
                $this->processValues();
                $sql = SqlBuilder::makeUpdate( $this );
                break;
            case 'DELETE':
                $sql = SqlBuilder::makeDelete( $this );
                break;
            case 'COUNT':
                $sql = SqlBuilder::makeCount( $this );
                break;
            default:
            case 'SELECT':
                $sql = SqlBuilder::makeSelect( $this );
                break;
        }
        $this->sql = $sql;
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
        if( !empty( $this->values ) )
        foreach( $this->values as $key => $val ) {
            if( $val === NULL ) {
                $this->functions[ $key ] = 'NULL';
                unset( $this->values[ $key ] );
            }
        }
        $values = $this->values;
        $this->prepOrQuote( $values );
        $this->rowData = array_merge( $this->functions, $values );
        return $this;
    }
    // +----------------------------------------------------------------------+
    /**
     * magic method to access Dba's method. 
     * 
     * @param $name
     * @param $args
     * @return mixed
     * @throws \RuntimeException
     */
    public function __call( $name, $args )
    {
        if( method_exists( $this->dba, $name ) ) {
            return call_user_func_array( array( $this->dba, $name ), $args );
        }
        throw new \RuntimeException( "Cannot access $name in Sql object." );
    }
    // +----------------------------------------------------------------------+
}