<?php
namespace wsCore\Dba;

class Dba
{
    /** @var \Pdo                        PDO object          */
    var $dbConn = NULL;
    /** @var \PdoStatement               PDO statement obj   */
    var $dbStmt = NULL;
    /** @var Sql                         Sql builder obj     */
    var $sql;

    private $fetchMode;
    private $fetchClass = NULL;
    // +----------------------------------------------------------------------+
    /**
     * @param \Pdo $dbConn
     * @param Sql  $sql
     */
    public function __construct( $dbConn=NULL, $sql=NULL )
    {
        $this->dbConn = ( is_object( $dbConn ) ) ?: Pdo::connect( $dbConn );
        $this->sql    = ( is_object( $sql ) ) ?: new Sql( $this );
        $this->fetchMode = \PDO::FETCH_ASSOC;
    }

    public function dbConnect( $conn=NULL, $new=FALSE ) {
        if( is_object( $conn ) ) {
            $this->dbConn = $conn;
        }
        elseif( $conn ) {
            $this->dbConn = ( $new ) ? Pdo::connectNew( $conn ): Pdo::connect( $conn );
        }
    }
    /**
     * @return Sql
     */
    public function sql() {
        return $this->sql;
    }

    /**
     * @return \PdoStatement
     */
    public function stmt() {
        return $this->dbStmt;
    }
    // +----------------------------------------------------------------------+
    /**
     * @param string $sql
     * @param array $prepared
     * @return Dba
     */
    public function execSQL( $sql, $prepared=array() )
    {
        if( strtoupper( substr( $sql, 0, 6 ) ) == 'SELECT' ) {
            return $this->query( $sql );
        }
        return $this->exec( $sql, $prepared );
    }

    /**
     * @param string $sql
     * @return Dba
     */
    public function query( $sql )
    {
        $this->dbStmt = $this->dbConn->query( $sql );
        return $this;
    }

    /**
     * @param string $sql
     * @param array $prepared
     * @return Dba
     */
    public function exec( $sql, $prepared=array() )
    {
        if( !empty( $prepared ) ) {
            $this->prepare( $sql, $prepared );
        }
        else {
            $this->dbConn->exec( $sql );
        }
        $this->dbStmt->setFetchMode( $this->fetchMode, $this->fetchClass );
        return $this;
    }

    /**
     * @param string $sql
     * @param array $prepared
     * @return Dba
     */
    public function prepare( $sql, $prepared ) {
        if( is_object( $this->dbStmt ) ) {
            $this->dbStmt->closeCursor();
        }
        $this->dbStmt = $this->dbConn->prepare( $sql, array(
            \PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL
        ) );
        $this->prepExec( $prepared );
        return $this;
    }

    /**
     * @param array $prepared
     * @return Dba
     */
    public function prepExec( $prepared ) {
        $this->dbStmt->execute( $prepared );
        return $this;
    }

    /**
     * @param integer $mode     \PDO's fetch mode
     * @param string $class       class name if mode is fetch_class
     * @return Dba
     */
    public function setFetchMode( $mode, $class=NULL ) {
        if( is_object( $this->dbStmt ) ) {
            $this->dbStmt->setFetchMode( $mode, $class );
        }
        $this->fetchMode  = $mode;
        $this->fetchClass = $class;
        return $this;
    }
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    public function lastId() {
        return $this->dbConn->lastInsertId();
    }

    /**
     * @return int|null|\PdoStatement
     */
    public function numRows() {
        if( is_numeric( $this->dbStmt ) ) {
            return $this->dbStmt;
        }
        return$this->dbStmt->rowCount();
    }

    /**
     * @return int|null|\PdoStatement
     */
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
}