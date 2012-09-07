<?php
namespace wsCore\Dba;

class Pdo
{
    /** @var array   stores configs for PDO construction */
    private static $configs = array();

    /** @var string  use the configs name as defaultName */
    private static $defaultName = '';

    /** @var array   default attributes for PDO driver  */
    private static $defaultAttr = array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_CASE => \PDO::CASE_LOWER,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
    );

    /** @var array   list of constructed PDO drivers    */
    private static $drivers = array();

    private static $pdo = '\PDO';
    // +----------------------------------------------------------------------+
    /**
     * set a named configs for database connection.
     * the first config name is used as default if no default name is set.
     *
     * @static
     * @param $name
     * @param $config
     */
    public static function set( $name, $config )
    {
        if( is_array( $config ) && isset( $config[ 'dsn' ] ) ) {
            $config = array_merge( $config, static::parseDbCon( $config[ 'dsn' ] ) );
        }
        elseif( is_string( $config ) ) {
            $config = static::parseDbCon( $config );
        }
        if( !isset( $config[ 'attributes' ] ) ) {
            $config[ 'attributes' ] = static::$defaultAttr;
        }
        if( !isset( $config[ 'username' ] ) ) {
            $config[ 'username' ] = '';
        }
        if( !isset( $config[ 'password' ] ) ) {
            $config[ 'password' ] = '';
        }
        static::$configs[ $name ] = $config;
        if( !isset( static::$defaultName ) ) {
            static::$defaultName = $name;
        }
    }

    /**
     * set a configs name as a defaultName
     *
     * @static
     * @param $name
     */
    public static function useConfig( $name ) {
        static::$defaultName = $name;
    }

    /**
     * returns Pdo connection which is pooled by config name.
     *
     * @static
     * @param string|null $name
     * @return Pdo
     * @throws \RuntimeException
     */
    public static function connect( $name=NULL )
    {
        $name = ( $name )?: static::$defaultName;
        if( isset( static::$drivers[ $name ] ) ) {
            return static::$drivers[ $name ];
        }
        return static::$drivers[ $name ] = static::connectNew( $name );
    }

    /**
     * always created brand new Pdo connection.
     *
     * @static
     * @param $name
     * @return Pdo
     * @throws \RuntimeException
     */
    public static function connectNew( $name ) {
        $name = ( $name )?: static::$defaultName;
        if( !isset( static::$configs[ $name ] ) ) {
            throw new \RuntimeException( "PDO Config name is not set.'" );
        }
        if( !isset( static::$configs[ $name ] ) ) {
            throw new \RuntimeException( "PDO Config '{$name}' is missing.'" );
        }
        return static::connectPdo( static::$configs[ $name ] );
    }

    /**
     * connect to database by PDO using $configs setting.
     * 
     * @static
     * @param array $config
     * @return Pdo
     */
    public static function connectPdo( $config )
    {
        //extract( $config );
        $dsn = "{$config{'db'}}:";
        $list = array( 'host', 'dbname', 'port' );
        foreach( $list as $item ) {
            if( isset( $config[ $item ] ) ) {
                $dsn .= "{$item}=" . $config[$item] . "; ";
            }
        }
        $class = static::$pdo;
        /** @var $pdo \PDO */
        $pdo   = new $class( $dsn, $config[ 'username' ], $config[ 'password' ], $config[ 'attributes' ] );
        if( isset( $config[ 'exec' ] ) ) {
            $pdo->exec( $config[ 'exec' ] );
        }
        return $pdo;
    }
    // +----------------------------------------------------------------------+
    /**
     * @static
     * @param $db_con
     * @return array
     */
    private static function parseDbCon( $db_con )
    {
        $conn_str = array( 'db', 'dbname', 'port', 'host', 'username', 'password' );
        $return_array = array();
        foreach( $conn_str as $parameter ) {
            $pattern = "/{$parameter}\s*=\s*(\S+)/";
            if( preg_match( $pattern, $db_con, $matches ) )
            {
                $return_array[ "{$parameter}" ] = $matches[1];
            }
        }
        return $return_array;
    }

    /**
     * sets PDO class name for testing, for example.
     * @static
     * @param $class
     */
    public static function setPdoClass( $class )
    {
        static::$pdo = $class;
    }
    // +----------------------------------------------------------------------+
}