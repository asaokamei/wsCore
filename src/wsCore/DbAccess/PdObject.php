<?php
namespace wsCore\DbAccess;

class PdObject
{
    /** @var \Pdo                        PDO object          */
    protected $pdoObj  = NULL;

    /** @var \PdoStatement */
    protected $pdoStmt;

    /** @var int                         fetch mode for PDO                */
    protected $fetchMode = \PDO::FETCH_ASSOC;
    
    /** @var string                      class name if fetch mode is Class */
    protected $fetchClass = NULL;
    // +----------------------------------------------------------------------+
    //  Constructor and Managing Objects.
    // +----------------------------------------------------------------------+
    /**
     * inject Pdo and Sql object.
     *
     * @param \Pdo $pdoObj
     * @DimInjection Get   Pdo
     */
    public function __construct( $pdoObj )
    {
        $this->pdoObj = $pdoObj;
    }

    /**
     * set Pdo. kind of for backward compatibility. 
     *
     * @param \Pdo $pdo
     * @return PdObject
     */
    public function dbConnect( $pdo ) {
        $this->pdoObj = $pdo;
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  Executing SQL. all methods returns Dba object.
    // +----------------------------------------------------------------------+
    /**
     * executes an SQL statement using prepare statement.
     *
     * @param string $sql
     * @param array  $prepared     place holders for prepared statement.
     * @param array  $dataTypes    data types for the place holders.
     * @throws \RuntimeException
     * @return \PdoStatement
     */
    public function exec( $sql, $prepared=array(), $dataTypes=array() )
    {
        if( !$sql ) throw new \RuntimeException( "missing Sql statement." );
        $this->execPrepare( $sql, $prepared );
        $this->execExecute( $prepared, $dataTypes );
        return $this->pdoStmt;
    }

    /**
     * @param string $sql
     * @return \PdoStatement
     */
    public function execPrepare( $sql ) {
        if( is_object( $this->pdoStmt ) ) {
            $this->pdoStmt->closeCursor();
        }
        $this->pdoStmt = $this->pdoObj->prepare( $sql, array(
            \PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL
        ) );
        return $this->pdoStmt;
    }

    /**
     * @param array  $prepared     place holders for prepared statement.
     * @param array  $dataTypes    data types for the place holders.
     * @return \PdoStatement
     */
    public function execExecute( $prepared, $dataTypes=array() ) 
    {
        if( $this->fetchMode ) {
            if( $this->fetchMode === \PDO::FETCH_CLASS ) {
                $this->pdoStmt->setFetchMode( $this->fetchMode, $this->fetchClass );
            }
            else {
                $this->pdoStmt->setFetchMode( $this->fetchMode );
            }
        }
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
        return $this->pdoStmt;
    }

    /**
     * @param integer $mode     \PDO's fetch mode
     * @param string $class       class name if mode is fetch_class
     * @return PdObject
     */
    public function setFetchMode( $mode, $class=NULL ) {
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

    // +----------------------------------------------------------------------+
    //  Miscellaneous methods.
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return PdObject
     */
    public function lockTable( $table ) {
        $lock = "LOCK TABLE {$table}";
        $driver = $this->getDriverName();
        if( $driver == 'pgsql' ) {
            $lock .= ' IN ACCESS EXCLUSIVE MODE';
        }
        $this->exec( $lock );
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
     * Quote string using Pdo's quote (or just add-slashes if Pdo not present).
     *
     * @param string|array $val
     * @return string|array
     */
    public function quote( $val )
    {
        if( is_array( $val ) ) {
            foreach( $val as &$v ) {
                $v = $this->quote( $v );
            }
        }
        elseif( isset( $this->pdoObj ) ) {
            $val = $this->pdoObj->quote( $val );
        }
        else {
            $val = "'" . addslashes( $val ) . "'";
        }
        return $val;
    }
    // +----------------------------------------------------------------------+
}