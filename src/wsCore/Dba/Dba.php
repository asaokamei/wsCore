<?php
namespace wsCore\Dba;

class Dba
{
    /** @var \Pdo                        PDO object          */
    var $pdoObj  = NULL;
    /** @var \PdoStatement               PDO statement obj   */
    var $pdoStmt = NULL;
    /** @var Sql                         Sql builder obj     */
    var $sql;

    private $fetchMode;
    private $fetchClass = NULL;
    private static $self=array();
    // +----------------------------------------------------------------------+
    /**
     * @param NULL|\Pdo $pdoObj
     * @param Sql  $sql
     */
    public function __construct( $pdoObj=NULL, $sql=NULL )
    {
        $this->pdoObj = ( is_object( $pdoObj ) ) ?: Rdb::connect( $pdoObj );
        $this->sql    = ( is_object( $sql ) ) ?: new Sql( $this );
        $this->fetchMode = \PDO::FETCH_ASSOC;
    }

    /**
     * TODO: is this method necessary?
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

    /**
     * connect to database. $conn maybe \Pdo object,
     * or connection string for Rdb::connect.
     *
     * @param \Pdo|string|null $conn
     * @param bool $new
     */
    public function dbConnect( $conn=NULL, $new=FALSE ) {
        if( is_object( $conn ) ) {
            $this->pdoObj = $conn;
        }
        elseif( $conn ) {
            $this->pdoObj = ( $new ) ? Rdb::connectNew( $conn ): Rdb::connect( $conn );
        }
    }

    /**
     * @return Sql
     */
    public function sql() {
        return $this->sql;
    }

    /**
     * @return \Pdo
     */
    public function pdo() {
        return $this->pdoObj;
    }
    /**
     * @return \PdoStatement
     */
    public function stmt() {
        return $this->pdoStmt;
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
        $this->pdoStmt = $this->pdoObj->query( $sql );
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
            $this->pdoObj->exec( $sql );
        }
        $this->pdoStmt->setFetchMode( $this->fetchMode, $this->fetchClass );
        return $this;
    }

    /**
     * @param string $sql
     * @return Dba
     */
    public function prepare( $sql ) {
        if( is_object( $this->pdoStmt ) ) {
            $this->pdoStmt->closeCursor();
        }
        $this->pdoStmt = $this->pdoObj->prepare( $sql, array(
            \PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL
        ) );
        return $this;
    }

    /**
     * @param array $prepared
     * @return Dba
     */
    public function execute( $prepared ) {
        $this->pdoStmt->execute( $prepared );
        return $this;
    }

    /**
     * @param integer $mode     \PDO's fetch mode
     * @param string $class       class name if mode is fetch_class
     * @return Dba
     */
    public function setFetchMode( $mode, $class=NULL ) {
        if( is_object( $this->pdoStmt ) ) {
            $this->pdoStmt->setFetchMode( $mode, $class );
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
        return $this->pdoObj->lastInsertId();
    }

    /**
     * @return int|null
     */
    public function numRows() {
        if( is_numeric( $this->pdoStmt ) ) {
            return $this->pdoStmt;
        }
        return$this->pdoStmt->rowCount();
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
     * @param $row
     * @return array|mixed
     */
    public function fetchRow( $row ) {
        if( is_object( $this->pdoStmt ) ) {
            return $this->pdoStmt->fetch( $this->fetchMode, \PDO::FETCH_ORI_ABS, $row );
        }
        return array();
    }

    /**
     * @param string $table
     * @return string
     */
    public function lockTable( $table ) {
        $lock = "LOCK TABLE {$table}";
        $driver = $this->pdoObj->getAttribute( \PDO::ATTR_DRIVER_NAME );
        if( $driver == 'pgsql' ) {
            $lock .= ' IN ACCESS EXCLUSIVE MODE';
        }
        $this->execSQL( $lock );
        return $this;
    }
}