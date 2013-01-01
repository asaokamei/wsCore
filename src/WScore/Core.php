<?php
namespace WScore;

class Core
{
    /** @var null|self */
    private static $_container = NULL;

    /** @var array      set easy mode */
    public static $easy = array(
        'Query'     => '\WScore\DbAccess\Query',
        'Validator' => '\WScore\Validator\Validator',
        'DataIO'    => '\WScore\Validator\DataIO',
        'Selector'  => '\WScore\Html\Selector',
        'EntityManager' => '\WScore\DataMapper\EntityManager',
        'Session'   => '\WScore\Web\Session',
        '' => '',
    );

    /** @var array      set development mode */
    public static $dev = array(
        '\WScore\DbAccess\DbAccess'   => '\WScore\Aspect\LogDba',
        '\WScore\Validator\Validator' => '\WScore\Aspect\LogValidator',
    );
    // +----------------------------------------------------------------------+
    /**
     *
     */
    public function __construct() {
    }

    /**
     * starts WScore Framework
     * @static
     * @return Core
     */
    public static function go() {
        ( static::$_container ) ?: static::$_container = new \WScore\DiContainer\Dimplet();
        self::_fill( self::$easy );
        static::$_container->set( 'Container', static::$_container );
        return static::$_container;
    }

    /**
     * going developer's mode.
     */
    public static function goDev() {
        self::go();
        self::set( 'devMode', TRUE );
        self::_fill( self::$dev );
    }
    
    public static function _fill( $fill ) {
        foreach( $fill as $id => $val ) {
            self::set( $id, $val );
        }
    }
    /**
     * @static
     */
    public static function clear() {
        static::$_container = NULL;
    }

    /**
     * @param string $id
     * @param mixed $val
     */
    public static function set( $id, $val ) {
        self::$_container->set( $id, $val );
    }
    /**
     * @param $id
     * @return mixed
     */
    public static function get( $id ) {
        return self::$_container->get( $id );
    }

    /**
     * set up for Pdo object.
     * 
     * @param string|array  $config
     * @param string        $id
     * @param null|string   $class
     * @param null|string   $method
     */
    public static function setPdo( $config, $id='Pdo', $class=NULL, $method= NULL )
    {
        return self::$_container->set( $id, $config );
    }
    // +----------------------------------------------------------------------+
}