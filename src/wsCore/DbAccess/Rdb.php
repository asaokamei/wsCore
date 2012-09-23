<?php
namespace wsCore\DbAccess;

/*

Sample usage with Core

Core::set( 'db.config', 'connection string' );
Core::setPdo( 'db.config' ); // will create 'Pdo' using db.config
Core::setPdo( 'db.config', 'Pdo2' ); // will create Pdo2 using db.config.

*/


class Rdb
{
    /** @var array   default attributes for PDO driver  */
    public $defaultAttr = array(
        \PDO::ATTR_ERRMODE      => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
    );
    
    /** @var string    Pdo class name to generate */
    public $pdoClass = '\PDO';

    /** @var string    charset to use. default is utf-8. */
    public $charset = 'utf8';
    
    // +----------------------------------------------------------------------+
    public function __construct() {
    }

    /**
     * returns Pdo connection which is pooled by config name.
     *
     * @param $config
     * @return \Pdo
     */
    public function connect( $config )
    {
        if( is_string( $config ) ) {
            // convert db connection string to array of config.
            $config = $this->parseDbCon( $config );
        }
        if( !isset( $config[ 'attributes' ] ) ) {
            $config[ 'attributes' ] = $this->defaultAttr;
        }
        if( !isset( $config[ 'username' ] ) ) {
            $config[ 'username' ] = NULL;
        }
        if( !isset( $config[ 'password' ] ) ) {
            $config[ 'password' ] = NULL;
        }
        return $this->connectPdo( $config );
    }

    /**
     * connect to database by PDO using $configs setting.
     *
     * @param array $config
     * @return \Pdo
     */
    public function connectPdo( $config )
    {
        $dsn = "{$config{'db'}}:";
        $list = array( 'host', 'dbname', 'port' );
        foreach( $list as $item ) {
            if( isset( $config[ $item ] ) ) {
                $dsn .= "{$item}=" . $config[$item] . "; ";
            }
        }
        $class = $this->pdoClass;
        $config[ 'dsn' ] = $dsn;
        /** @var $pdo \PDO */
        $pdo = new $class( $dsn, $config[ 'username' ], $config[ 'password' ], $config[ 'attributes' ] );
        if( isset( $config[ 'exec' ] ) ) {
            $pdo->exec( $config[ 'exec' ] );
        }
        return $pdo;
    }
    // +----------------------------------------------------------------------+
    /**
     * parses db connection string to config array.
     * 
     * @param string $db_con
     * @return array
     */
    private function parseDbCon( $db_con )
    {
        $conn_str = array( 'db', 'dbname', 'port', 'host', 'username', 'password', 'charset' );
        $return_array = array();
        foreach( $conn_str as $parameter ) 
        {
            $pattern = "/{$parameter}\s*=\s*(\S+)/";
            if( preg_match( $pattern, $db_con, $matches ) ) {
                $return_array[ "{$parameter}" ] = $matches[1];
            }
        }
        // charset is for PHP5.3.6 or above
        if( !isset( $return_array[ 'charset' ] ) ) $return_array[ 'charset' ] = $this->charset;
        return $return_array;
    }
    // +----------------------------------------------------------------------+
}