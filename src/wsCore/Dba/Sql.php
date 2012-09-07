<?php
namespace wsCore\Dba;

class Sql
{
    // public variables to represent sql statement.
    var $table;
    var $join = array();
    var $columns;
    var $values = array();
    var $functions = array();
    var $order;
    var $where;
    var $group;
    var $having;
    var $misc;
    var $limit = FALSE;
    var $offset = 0;
    var $distinct = FALSE;
    var $forUpdate = FALSE;

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
     * @return \PdoStatement
     */
    public function exec() {
        $this->dba->execSQL( $this->sql, $this->prepared_values );
        return $this->dba->stmt();
    }

    /**
     * executes SQL statement. mostly for backward compatibility.
     *
     * @param null $sql
     * @param null $prepared
     * @return \PdoStatement
     */
    public function execSQL( $sql=NULL, $prepared=NULL ) {
        $sql = ( $sql ) ?: $this->sql;
        $this->dba->execSQL( $sql, $prepared );
        return $this->dba->stmt();
    }
    // +----------------------------------------------------------------------+
    public function prepValue( &$val ) {
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
    public function quote( $val ) {
        if( is_array( $val ) ) {
            foreach( $val as $k => $v ) {
                $val[ $k ] = $this->quote( $v );
            }
        }
        else {
            if( is_object( $this->dba->dbConn ) ) {
                $val = $this->dba->dbConn->quote( $val );
            }
            $val = addslashes( $val );
        }
        return $val;
    }
    public function p( $val ) {
        $this->prepValue( $val );
        return $val;
    }
    public function q( &$val ) {
        $val = '\'' . $this->quote( $val ) . '\'';
        return $val;
    }
    // +----------------------------------------------------------------------+
    public function table( $table ) {
        $this->table = $table;
        return $this;
    }
    public function column( $column ) {
        $this->columns = $column;
        return $this;
    }
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
    public function offset( $offset, $limit=FALSE ) {
        $this->offset = ( is_numeric( $offset ) ) ? $offset: 0;
        return $this;
    }
    public function distinct(){
        $this->distinct = TRUE;
        return $this;
    }
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
    public function where( $col, $val, $rel='=', $op='' ) {
        $where = array( 'col' => $col, 'val'=> $val, 'rel' => $rel, 'op' => $op );
        $this->where[] = $where;
        return $this;
    }
    public function setWhere( $where ) {
        $this->where = $where;
        return $this;
    }
    public function addWhere( $where, $op='AND' ) {
        return $this->where( $where, '', '', $op );
    }
    public function clearWhere() {
        $this->where = NULL;
        return $this;
    }
    // +----------------------------------------------------------------------+
    public function update( $values ) {
        return $this->values( $values )
            ->makeSQL( 'UPDATE' )
            ->exec();
    }
    public function insert( $values ) {
        return $this->values( $values )
            ->makeSQL( 'INSERT' )
            ->exec();
    }
    public function select( $column=NULL ) {
        if( $column ) $this->column( $column );
        $this->makeSQL( 'SELECT' )
            ->exec();
    }
    public function count() {
        return $this->makeSQL( 'COUNT' )
            ->exec()
            ->fetchColumn(0);
    }
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
    public function makeWhere() {
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
    public function processValues() {
        if( !empty( $this->values ) )
        foreach( $this->values as $key => $val ) {
            if( $val === NULL ) {
                $this->functions[ $key ] = 'NULL';
                unset( $this->values[ $key ] );
            }
        }
        return array_merge( $this->functions, $this->p( $this->values ) );
    }
    public function makeInsert() {
        if( !$this->table ) throw new \RuntimeException( 'table not set. ' );
        $values = $this->processValues();
        $listV = implode( ', ', $values );
        $listC = implode( ', ', array_keys( $values ) );
        return "INSERT INTO {$this->table} ( {$listC} ) VALUES ( {$listV} )";
    }
    public function makeUpdate() {
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
    public function makeDelete() {
        if( !$this->table ) throw new \RuntimeException( 'table not set. ' );
        if( !$where = $this->makeWhere() ) {
            throw new \RuntimeException( 'Cannot delete without where condition. ' );
        }
        return "DELETE FROM {$this->table} WHERE " . $where;
    }
    public function makeCount() {
        $column = $this->columns;
        $update = $this->forUpdate;
        $this->columns   = 'COUNT(*) AS wscore__count__';
        $this->forUpdate = FALSE;
        $select = $this->makeSelect();
        $this->columns   = $column;
        $this->forUpdate = $update;
        return $select;
    }
    public function makeSelect() {
        $select = 'SELECT '
            . ( $this->distinct ) ? 'DISTINCT ': ''
            . $this->makeSelectBody()
            . ( $this->forUpdate ) ? ' FOR UPDATE': '';
        return $select;
    }
    public function makeSelectBody() {
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
    public function makeJoin() {
        $joined = '';
        if( !empty( $this->join ) )
        foreach( $this->join as $join ) {
            $joined .= $join . ' ';
        }
        return $joined;
    }
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
    // +----------------------------------------------------------------------+
}