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
    
    /** @var array    stores data types of place holders     */
    var $prepared_types = array();

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
        return new self( $this->dba );
    }

    /**
     * executes SQL statement.
     *
     * @throws \RuntimeException
     * @return Dba
     */
    public function exec() {
        return $this->dba->execSQL( $this->sql, $this->prepared_values, $this->prepared_types );
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
     * pre-process values with prepare or quote method.
     *
     * @param      $val
     * @param null $type    data type
     * @param null $col     column name. used to find data type
     * @return Sql
     */
    public function prepOrQuote( &$val, $type=NULL, $col=NULL )
    {
        $pqType = ( $this->prepQuoteUseType )?: static::$pqDefault;
        $this->$pqType( $val, $type );
        return $this;
    }

    /**
     * replaces value with place holder for prepared statement. 
     * the value is kept in prepared_value array.
     * 
     * if $type is specified, or column data type is set in col_data_types, 
     * types for the place holder is kept in prepared_types array.
     *
     * @param string|array $val
     * @param null|int     $type    data type
     * @param null $col     column name. used to find data type
     * @return Sql
     */
    public function prepare( &$val, $type=NULL, $col=NULL )
    {
        if( is_array( $val ) ) {
            foreach( $val as &$v ) {
                $this->prepare( $v, $type, $col );
            }
        }
        else {
            $holder = ':db_prep_' . count( $this->prepared_values );
            $this->prepared_values[ $holder ] = $val;
            $val = $holder;
            if( $type ) {
                $this->prepared_types[ $holder ] = $type;
            }
            elseif( !$type && array_key_exists( $col, $this->col_data_types ) ) {
                $this->prepared_types[ $holder ] = $this->col_data_types[ $col ];
            }
        }
        return $this;
    }

    /**
     * Quote string using Pdo's quote (or just add-slashes if Pdo not present). 
     * 
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
            $val = $this->dba->quote( $val );
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
     * TODO: find better name than functions??? how about rawValue?
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
     * set where statement with values properly prepared/quoted. 
     * 
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param null|string|bool   $type
     * @return Sql
     */
    public function where( $col, $val, $rel='=', $type=NULL ) {
        $this->prepOrQuote( $val, $type, $col );
        return $this->whereRaw( $col, $val, $rel, $type );
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
        $where = array( 'col' => $col, 'val'=> $val, 'rel' => $rel, 'op' => 'AND' );
        $this->where[] = $where;
        return $this;
    }

    /**
     * sets OR operation for the last where statement data. 
     * 
     * @return Sql
     */
    public function or_() {
        $last = array_pop( $this->where );
        if( $last ) {
            $last[ 'op' ] = 'OR';
            array_push( $this->where, $last );
        }
        return $this;
    }
    
    public function eq( $col, $val, $type=NULL ) {
        return $this->where( $col, $val, '=', $type );
    }
    public function ne( $col, $val, $type=NULL ) {
        return $this->where( $col, $val, '!=', $type );
    }
    public function lt( $col, $val, $type=NULL ) {
        return $this->where( $col, $val, '<', $type );
    }
    public function le( $col, $val, $type=NULL ) {
        return $this->where( $col, $val, '<=', $type );
    }
    public function gt( $col, $val, $type=NULL ) {
        return $this->where( $col, $val, '>', $type );
    }
    public function ge( $col, $val, $type=NULL ) {
        return $this->where( $col, $val, '>=', $type );
    }
    public function isNull( $col ) {
        return $this->whereRaw( $col, '', 'IS NULL' );
    }
    public function notNull( $col ) {
        return $this->whereRaw( $col, '', 'NOT NULL' );
    }
    public function like( $col, $val, $type=NULL ) {
        return $this->where( $col, "%{$val}%", 'LIKE', $type );
    }
    public function startWith( $col, $val, $type=NULL ) {
        return $this->where( $col, $val.'%', 'LIKE', $type );
    }
    public function endWith( $col, $val, $type=NULL ) {
        return $this->where( $col, '%'.$val, 'LIKE', $type );
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
     * @return Sql
     */
    public function addWhere( $where ) {
        return $this->where( $where, '', '' );
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
        foreach( $values as $col => &$val ) {
            $this->prepOrQuote( $val, NULL, $col );
        }
        $this->rowData = array_merge( $this->functions, $values );
        return $this;
    }
    // +----------------------------------------------------------------------+
}