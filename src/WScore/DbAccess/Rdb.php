<?php
namespace wsCore\DbAccess;

/*

Sample usage with Core

Core::set( 'db.config', 'connection string' );
Core::setPdo( 'db.config' ); // will create 'Pdo' using db.config
Core::setPdo( 'db.config', 'Pdo2' ); // will create Pdo2 using db.config.

config = array(
    'dsn'  => 'db:dbname=dbname; host=host; port=port; charset=utf8',
    'username' => 'user name',
    'password' => 'password',
    'execute'  => 'sql to execute',
    'attributes' => [ attr => val, ... ]
);

OR

config = 'db=database dbname=dbname host=host port=port username=user password=pswd';

*/


class Rdb
{
    /** @var array   default attributes for PDO driver  */
    public $defaultAttr = array(
        \PDO::ATTR_ERRMODE      => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
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
     * @throws \RuntimeException
     * @return \Pdo
     */
    public function connect( $config )
    {
        if( empty( $config ) ) {
            throw new \RuntimeException( 'empty config for Pdo.' );
        }
        if( is_string( $config ) ) {
            $config = $this->parseDbCon( $config );
        }
        if( !isset( $config[ 'dsn' ] ) || empty( $config[ 'dsn' ] ) ) {
            throw new \RuntimeException( 'dsn not set for Pdo.' );
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
        $class = $this->pdoClass;
        /** @var $pdo \PDO */
        $pdo = new $class( $config[ 'dsn' ], $config[ 'username' ], $config[ 'password' ], $config[ 'attributes' ] );
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
        $conn_str = array( 'dsn', 'db', 'dbname', 'port', 'host', 'username', 'password', 'charset' );
        $config = array();
        foreach( $conn_str as $parameter ) 
        {
            $pattern = "/{$parameter}=(\S+)/";
            if( preg_match( $pattern, $db_con, $matches ) ) {
                $config[ "{$parameter}" ] = $matches[1];
                $db_con = preg_replace( "/{$parameter}={$matches{1}}/", '', $db_con );
            }
        }
        // build dsn
        if( !isset( $config[ 'dsn' ] ) ) {
            $dsn = "{$config{'db'}}:";
            $list = array( 'host', 'dbname', 'port', 'charset' );
            foreach( $list as $item ) {
                if( isset( $config[ $item ] ) ) {
                    $dsn .= "{$item}=" . $config[$item] . ";";
                }
            }
            $config[ 'dsn' ] = $dsn;
        }
        return $config;
    }
    // +----------------------------------------------------------------------+
}