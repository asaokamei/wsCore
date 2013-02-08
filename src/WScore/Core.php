<?php
namespace WScore;

use \WScore\DiContainer\Dimplet;

class Core
{
    /** @var \WScore\DiContainer\Dimplet */
    private static $_container = null;

    private static $_cache = null;

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
     * starts WScore Framework
     * @static
     * @return Core
     */
    public static function go() 
    {
        if( !isset( self::$_container ) ) {
            self::newDiC();
        }
        self::_fill( self::$easy );
        self::$_container->set( 'Container', self::$_container );
        return self::$_container;
    }

    public static function newDiC()
    {
        self::$_cache = \WScore\DiContainer\Cache::getCache();
        self::$_container = new Dimplet(
            new \WScore\DiContainer\Pimplet(),
            new \WScore\DiContainer\Forge( self::$_cache )
        );
    }

    /**
     * going developer's mode.
     */
    public static function goDev() {
        self::go();
        self::set( 'devMode', true );
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
        self::$_container = null;
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
     * @param string $id
     * @param \Closure $func
     */
    public static function extend( $id, $func ) {
        self::$_container->extend( $id, $func );
    }

    /**
     * set up for Pdo object.
     * 
     * @param string|array  $config
     * @param string        $id
     * @param null|string   $class
     * @param null|string   $method
     */
    public static function setPdo( $config, $id='Pdo', $class=null, $method= null )
    {
        self::$_container->set( $id, $config );
    }
    // +----------------------------------------------------------------------+
}