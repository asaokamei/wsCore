<?php
namespace wsCore\Dba;

class Sql
{
    /** @var \Pdo   PDO object  */
    var $dbConn = NULL;
    /** @var \PdoStatement               PDO statement obj */
    var $dbStmt = NULL;

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
    var $limit = FALSE;
    var $offset = 0;
    var $distinct = FALSE;

    var $sql = '';
    var $prepared_values = array();
    var $fetchMode;
    // +----------------------------------------------------------------------+
    public function __construct( $dbConn=NULL )
    {
        $this->dbConn = ( is_object( $dbConn ) ) ?: Pdo::connect( $dbConn );
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
            if( is_object( $this->dbConn ) ) {
                $val = $this->dbConn->quote( $val );
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
    public function where( $col, $val, $rel='=', $op='' ) {
        $where = array( 'col' => $col, 'val'=> $val, 'rel' => $rel, 'op' => $op );
        $this->where[] = $where;
        return $this;
    }
    public function setWhere( $where ) {
        $this->where = $where;
        return $this;
    }
    // +----------------------------------------------------------------------+
    public function makeWhere( $whereList ) {
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
        $values = $this->processValues();
        $listV = implode( ', ', $values );
        $listC = implode( ', ', array_keys( $values ) );
        return "INSERT INTO {$this->table} ( {$listC} ) VALUES ( {$listV} )";
    }
    // +----------------------------------------------------------------------+
    public function execSQL( $sql=NULL )
    {
        $sql = ( $sql ) ?: $this->sql;
        if( strtoupper( substr( $sql, 0, 6 ) ) == 'SELECT' ) {
            return $this->query( $sql );
        }
        return $this->exec( $sql );
    }
    public function query( $sql=NULL )
    {
        $sql = ( $sql ) ?: $this->sql;
        $this->dbStmt = $this->dbConn->query( $sql );
        return $this;
    }
    public function exec( $sql=NULL )
    {
        $sql = ( $sql ) ?: $this->sql;
        if( !empty( $this->prepared_values ) ) {
            $this->prepare( $sql, $this->prepared_values );
        }
        else {
            $this->dbConn->exec( $sql );
        }
        $this->setFetchMode( \PDO::FETCH_ASSOC );
        return $this;
    }
    public function prepare( $sql, $prepared_values ) {
        if( is_object( $this->dbStmt ) ) {
            $this->dbStmt->closeCursor();
        }
        $this->dbStmt = $this->dbConn->prepare( $sql, array(
            \PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL
        ) );
        $this->dbStmt->execute( $prepared_values );
        return $this;
    }
    public function setFetchMode( $mode, $class=NULL ) {
        $this->dbStmt->setFetchMode( $mode, $class );
        $this->fetchMode = $mode;
        return $this;
    }
    // +----------------------------------------------------------------------+
    public function lastId() {
        return $this->dbConn->lastInsertId();
    }
    public function numRows() {
        if( is_numeric( $this->dbStmt ) ) {
            return $this->dbStmt;
        }
        return$this->dbStmt->rowCount();
    }
    public function fetchNumRow() {
        return $this->numRows();
    }
    public function fetchAll( &$data ) {
        if( is_object( $this->dbStmt ) ) {
            return $this->dbStmt->fetchAll();
        }
        return array();
    }
    public function fetchRow( $row ) {
        if( is_object( $this->dbStmt ) ) {
            return $this->dbStmt->fetch( $this->fetchMode, \PDO::FETCH_ORI_ABS, $row );
        }
        return array();
    }
    // +----------------------------------------------------------------------+
}