<?php
namespace wsCore\DbAccess;

class Sql
{
    // public variables to represent sql statement.
    /** @var string           name of database table    */
    var $table;

    /** @var array            join for table            */
    var $join = array();

    /** @var string|array     columns to select in array or string     */
    var $columns;

    /** @var array            values for insert/update in array        */
    var $values = array();

    /** @var array            sql functions for insert/update          */
    var $functions = array();

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

    /** @var string   SQL Statement created by this class    */
    var $sql = '';

    /** @var Dba      DataBase Access object                 */
    private $dba;

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
     * @return \PdoStatement
     */
    public function exec() {
        if( !$this->dba ) throw new \RuntimeException( 'DbAccess object not set to perform this method.' );
        $this->dba->execSQL( $this->sql, $this->prepared_values );
        return $this->dba->stmt();
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
    /**
     * replaces value with holder for prepared statement. the value is kept
     * inside prepared_value array.
     *
     * @param string|array $val
     * @return Sql
     */
    public function prepValue( &$val )
    {
        if( is_array( $val ) ) {
            foreach( $val as $k => $v ) {
                $this->prepValue( $v );
                $val[ $k ] = $v;
            }
        }
        else {
            $holder = ':db_prep_' . count( $this->prepared_values );
            $this->prepared_values[ $holder ] = $val;
            $val = $holder;
        }
        return $this;
    }

    /**
     * @param string|array $val
     * @param bool|\Pdo $pdo
     * @return array|string
     */
    public function quote( $val, $pdo=FALSE )
    {
        // try to get Pdo only once. false used only here... I think.
        if( $pdo === FALSE ) $pdo = $this->dba->pdo();
        if( is_array( $val ) ) {
            foreach( $val as $k => $v ) {
                $val[ $k ] = $this->quote( $v, $pdo );
            }
        }
        elseif( $pdo ) {
            $val = $pdo->quote( $val );
        }
        else {
            $val = addslashes( $val );
        }
        return $val;
    }

    /**
     * @param string $val
     * @return mixed
     */
    public function p( $val ) {
        $this->prepValue( $val );
        return $val;
    }

    /**
     * @param string $val
     * @return string
     */
    public function q( &$val ) {
        $val = '\'' . $this->quote( $val ) . '\'';
        return $val;
    }
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return Sql
     */
    public function table( $table ) {
        $this->table = $table;
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
    /**
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param string $op
     * @return Sql
     */
    public function where( $col, $val, $rel='=', $op='' ) {
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
     */
    public function select( $column=NULL ) {
        if( $column ) $this->column( $column );
        $this->makeSQL( 'SELECT' )
            ->exec();
    }

    /**
     * @return string
     */
    public function count() {
        return $this->makeSQL( 'COUNT' )
            ->exec()
            ->fetchColumn(0);
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
                $sql = $this->makeInsert();
                break;
            case 'UPDATE':
                $sql = $this->makeUpdate();
                break;
            case 'DELETE':
                $sql = $this->makeDelete();
                break;
            case 'COUNT':
                $sql = $this->makeCount();
                break;
            default:
            case 'SELECT':
                $sql = $this->makeSelect();
                break;
        }
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return string
     */
    public function makeWhere()
    {
        if( is_array( $this->where ) ) {
            $where = '';
            foreach( $this->where as $wh ) {
                $where .= call_user_func_array( array( $this, 'formWhere' ), $wh );
            }
        }
        else {
            $where = $this->where;
        }
        $where = trim( $where );
        preg_replace( '/^(AND|OR)/i', '', $where );
        return $where;
    }

    /**
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param string $op
     * @return string
     */
    public function formWhere( $col, $val, $rel='=', $op='AND' ) {
        $where = '';
        $rel = strtoupper( $rel );
        if( $rel == 'IN' ) { 
            $val = "( " . is_array( $val ) ? implode( ", ", $val ): "{$val}" . " )";
        }
        elseif( $rel = 'BETWEEN' ) {
            $val = "{$val{0}} AND {$val{1}}";
        }
        elseif( $col == '(' ) {
            $val = $rel = '';
        }
        elseif( $col == ')' ) {
            $op = $rel = $val = '';
        }
        $where .= trim( "{$op} {$col} {$rel} {$val}" ) . ' ';
        return $where;
    }

    /**
     * prepares value for prepared statement. if value is NULL,
     * it will not be treated as prepared value, instead it is
     * set to SQL's NULL value.
     * @return array
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
        return array_merge( $this->functions, $this->p( $this->values ) );
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function makeInsert()
    {
        if( !$this->table ) throw new \RuntimeException( 'table not set. ' );
        $values = $this->processValues();
        $listV = implode( ', ', $values );
        $listC = implode( ', ', array_keys( $values ) );
        return "INSERT INTO {$this->table} ( {$listC} ) VALUES ( {$listV} )";
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function makeUpdate()
    {
        if( !$this->table ) throw new \RuntimeException( 'table not set. ' );
        $list   = array();
        $values = $this->processValues();
        foreach( $values as $col => $val ) {
            $list[] = "{$col}={$val}";
        }
        $sql  = "UPDATE {$this->table} SET " . implode( ', ', $list );
        $sql .= ( $where=$this->makeWhere() ) ? " WHERE {$where}" : '';
        return $sql;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function makeDelete()
    {
        if( !$this->table ) throw new \RuntimeException( 'table not set. ' );
        if( !$where = $this->makeWhere() ) {
            throw new \RuntimeException( 'Cannot delete without where condition. ' );
        }
        return "DELETE FROM {$this->table} WHERE " . $where;
    }

    /**
     * @return string
     */
    public function makeCount()
    {
        $column = $this->columns;
        $update = $this->forUpdate;
        $this->columns   = 'COUNT(*) AS wscore__count__';
        $this->forUpdate = FALSE;
        $select = $this->makeSelect();
        $this->columns   = $column;
        $this->forUpdate = $update;
        return $select;
    }

    /**
     * @return string
     */
    public function makeSelect()
    {
        $select = 'SELECT '
            . ( $this->distinct ) ? 'DISTINCT ': ''
            . $this->makeSelectBody()
            . ( $this->forUpdate ) ? ' FOR UPDATE': '';
        return $select;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function makeSelectBody()
    {
        if( !$this->table ) throw new \RuntimeException( 'table not set. ' );
        $select  = $this->makeColumn();
        $select .= ' FROM ' . $this->table;
        $select .= $this->makeJoin();
        $select .= ( $where = $this->makeWhere() ) ? ' WHERE '.$where: '';
        $select .= ( $this->group  ) ? ' GROUP BY '   . $this->group: '';
        $select .= ( $this->having ) ? ' HAVING '     . $this->having: '';
        $select .= ( $this->order  ) ? ' ORDER BY '   . $this->order: '';
        $select .= ( $this->misc   ) ? ' '            . $this->misc: '';
        $select .= ( $this->limit  > 0 ) ? ' LIMIT '  . $this->limit: '';
        $select .= ( $this->offset > 0 ) ? ' OFFSET ' . $this->offset: '';
        return $select;
    }

    /**
     * @return string
     */
    public function makeJoin() {
        $joined = '';
        if( !empty( $this->join ) )
        foreach( $this->join as $join ) {
            $joined .= $join . ' ';
        }
        return $joined;
    }

    /**
     * @return string
     */
    public function makeColumn() {
        if( empty( $this->columns ) ) {
            $column = '*';
        }
        elseif( is_array( $this->columns ) ) {
            $column = implode( ', ', $this->columns );
        }
        else {
            $column = $this->columns;
        }
        return $column;
    }

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