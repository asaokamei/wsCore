<?php
namespace WScore\DbAccess;

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
    public static $defaultAttr = array(
        \PDO::ATTR_ERRMODE      => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    );

    /** @var string    Pdo class name to generate */
    public static $pdoClass = '\PDO';

    /** @var string    charset to use. default is utf-8. */
    public static $charset = 'utf8';
    
    // +----------------------------------------------------------------------+

    /**
     * returns Pdo connection which is pooled by config name.
     *
     * @param $config
     * @throws \RuntimeException
     * @return \Pdo
     */
    public static function connect( $config )
    {
        if( is_string( $config ) ) {
            $config = static::prepare( $config );
        }
        if( !isset( $config[ 'dsn' ] ) || empty( $config[ 'dsn' ] ) ) {
            throw new \RuntimeException( 'dsn not set for Pdo.' );
        }
        if( !isset( $config[ 'attributes' ] ) ) {
            $config[ 'attributes' ] = static::$defaultAttr;
        }
        if( !isset( $config[ 'username' ] ) ) {
            $config[ 'username' ] = NULL;
        }
        if( !isset( $config[ 'password' ] ) ) {
            $config[ 'password' ] = NULL;
        }
        $class = static::$pdoClass;
        /** @var $pdo \Pdo */
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
    public static function prepare( $db_con )
    {
        $conn_str = array( 'dsn', 'username', 'password' );
        $config = array();
        foreach( $conn_str as $parameter ) 
        {
            $pattern = "/{$parameter}=(\S+)/";
            if( preg_match( $pattern, $db_con, $matches ) ) {
                $config[ "{$parameter}" ] = $matches[1];
                $db_con = preg_replace( "/{$parameter}={$matches{1}}/", '', $db_con );
            }
        }
        return $config;
    }
    // +----------------------------------------------------------------------+
}