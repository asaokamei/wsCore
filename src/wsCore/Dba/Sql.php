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
    var $values;
    var $functions;
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
    public function prepVal( &$val ) {

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
    public function limit( $limit, $offset=FALSE ) {
        $this->limit  = ( $limit  ) ? $limit : FALSE;
        $this->offset = ( is_numeric( $offset ) ) ? $offset: 0;
        return $this;
    }
    public function offset( $offset, $limit=FALSE ) {
        return $this->limit( $limit, $offset );
    }
    public function setFetchMode( $mode, $class=NULL ) {
        $this->dbStmt->setFetchMode( $mode, $class );
        $this->fetchMode = $mode;
        return $this;
    }
    // +----------------------------------------------------------------------+
    public function quote( $val ) {
        if( is_object( $this->dbConn ) ) {
            return $this->dbConn->quote( $val );
        }
        return addslashes( $val );
    }
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