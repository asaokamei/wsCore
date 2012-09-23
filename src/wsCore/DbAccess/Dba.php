<?php
namespace wsCore\DbAccess;

class Dba implements InjectSqlInterface
{
    /** @var \Pdo                        PDO object          */
    var $pdoObj  = NULL;

    /** @var \PdoStatement               PDO statement obj   */
    var $pdoStmt = NULL;

    /** @var \wsCore\DbAccess\Sql        Sql builder obj     */
    var $sql     = NULL;

    /** @var int                         fetch mode for PDO                */
    private $fetchMode;
    /** @var string                      class name if fetch mode is Class */
    private $fetchClass = NULL;
    // +----------------------------------------------------------------------+
    //  Constructor and Managing Objects.
    // +----------------------------------------------------------------------+
    /**
     * inject Pdo object, or config name for Rdb::connect.
     * set $new=TRUE with config name to use brand-new Pdo object.
     *
     * @param NULL|string|\Pdo $pdoObj
     * @param bool $new
     */
    public function __construct( $pdoObj=NULL, $new=FALSE )
    {
        $this->pdoObj = ( is_object( $pdoObj ) ) ?: Rdb::connect( $pdoObj, $new );
        $this->fetchMode = \PDO::FETCH_ASSOC;
    }

    /**
     * @param \wsCore\DbAccess\Sql $sql
     */
    public function injectSql( $sql ) {
        $this->sql = $sql;
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
        else {
            $this->pdoObj = Rdb::connect( $conn, $new );
        }
    }

    /**
     * @return Sql
     */
    public function sql() {
        $this->sql = ( $this->sql )? $this->sql->clear() : new Sql( $this );
        return $this->sql;
    }
    public function table( $table, $id_name='id' ) {
        return $this->sql()->table( $table, $id_name );
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
    //  Executing SQL. all methods returns Dba object.
    // +----------------------------------------------------------------------+
    /**
     * @param string $sql
     * @param array  $prepared     place holders for prepared statement.
     * @param array  $dataTypes    data types for the place holders.
     * @return Dba
     */
    public function execSQL( $sql, $prepared=array(), $dataTypes=array() )
    {
        return $this->exec( $sql, $prepared, $dataTypes );
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
     * @param array  $prepared     place holders for prepared statement.
     * @param array  $dataTypes    data types for the place holders. 
     * @return Dba
     */
    public function exec( $sql, $prepared=array(), $dataTypes=array() )
    {
        $this->prepare( $sql, $prepared );
        $this->execute( $prepared, $dataTypes );
        //$this->pdoStmt->setFetchMode( $this->fetchMode, $this->fetchClass );
        //$this->pdoStmt->setFetchMode( $this->fetchMode );
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
     * @param array  $prepared     place holders for prepared statement.
     * @param array  $dataTypes    data types for the place holders.
     * @return Dba
     */
    public function execute( $prepared, $dataTypes=array() ) 
    {
        if( empty( $dataTypes ) ) {
            // data types are not specified. just execute the statement. 
            $this->pdoStmt->execute( $prepared );
        }
        else {
            // bind value for each holder/value.
            foreach( $prepared as $holder => $value ) {
                if( array_key_exists( $holder, $dataTypes ) ) {
                    // data types for the holder specified. 
                    $this->pdoStmt->bindValue( $holder, $value, $dataTypes[ $holder ] );
                }
                else {
                    $this->pdoStmt->bindValue( $holder, $value );
                }
            }
        }
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
    //  fetching result from the database.
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
        return $this->pdoStmt->rowCount();
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
     * @param int $row
     * @throws \RuntimeException
     * @return array|mixed
     */
    public function fetchRow( $row=0 ) {
        if( is_object( $this->pdoStmt ) ) {
            if( $row > 0 ) {
                $driver = $this->getDriverName();
                if( $driver == 'mysql' || $driver == 'sqlite' ) {
                    throw new \RuntimeException( "Cannot fetch with offset for ".$driver );
                }
            }
            return $this->pdoStmt->fetch( $this->fetchMode, \PDO::FETCH_ORI_ABS, $row );
        }
        return array();
    }
    // +----------------------------------------------------------------------+
    //  Miscellaneous methods.
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return string
     */
    public function lockTable( $table ) {
        $lock = "LOCK TABLE {$table}";
        $driver = $this->getDriverName();
        if( $driver == 'pgsql' ) {
            $lock .= ' IN ACCESS EXCLUSIVE MODE';
        }
        $this->execSQL( $lock );
        return $this;
    }

    /**
     * get driver name, such as mysql, sqlite, pgsql.
     * @return string
     */
    public function getDriverName() {
        return $this->pdoObj->getAttribute( \PDO::ATTR_DRIVER_NAME );
    }

    /**
     * magic method to access Sql's method.
     *
     * @param $name
     * @param $args
     * @return mixed
     * @throws \RuntimeException
     */
    public function __call( $name, $args )
    {
        if( method_exists( $this->sql, $name ) ) {
            return call_user_func_array( array( $this->sql, $name ), $args );
        }
        throw new \RuntimeException( "Cannot access $name in Dba object." );
    }
}