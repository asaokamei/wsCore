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
    private static $self=array();
    // +----------------------------------------------------------------------+
    /**
     * @param NULL|\Pdo $dbConn
     * @param Sql  $sql
     */
    public function __construct( $dbConn=NULL, $sql=NULL )
    {
        $this->dbConn = ( is_object( $dbConn ) ) ?: Rdb::connect( $dbConn );
        $this->sql    = ( is_object( $sql ) ) ?: new Sql( $this );
        $this->fetchMode = \PDO::FETCH_ASSOC;
    }

    /**
     * @static
     * @return Dba
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if( isset( static::$self[ $class ] ) ) {
            return static::$self[ $class ];
        }
        return static::$self[ $class ] = new static();
    }
    public function dbConnect( $conn=NULL, $new=FALSE ) {
        if( is_object( $conn ) ) {
            $this->dbConn = $conn;
        }
        elseif( $conn ) {
            $this->dbConn = ( $new ) ? Rdb::connectNew( $conn ): Rdb::connect( $conn );
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
            $this->execute( $prepared );
        }
        else {
            $this->dbConn->exec( $sql );
        }
        $this->dbStmt->setFetchMode( $this->fetchMode, $this->fetchClass );
        return $this;
    }

    /**
     * @param string $sql
     * @return Dba
     */
    public function prepare( $sql ) {
        if( is_object( $this->dbStmt ) ) {
            $this->dbStmt->closeCursor();
        }
        $this->dbStmt = $this->dbConn->prepare( $sql, array(
            \PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL
        ) );
        return $this;
    }

    /**
     * @param array $prepared
     * @return Dba
     */
    public function execute( $prepared ) {
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
    public function fetchAll() {
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

    /**
     * @param string $table
     * @return string
     */
    public function lockTable( $table ) {
        $lock = "LOCK TABLE {$table}";
        $driver = $this->dbConn->getAttribute( \PDO::ATTR_DRIVER_NAME );
        if( $driver == 'pgsql' ) {
            $lock .= ' IN ACCESS EXCLUSIVE MODE';
        }
        $this->execSQL( $lock );
        return $this;
    }
}