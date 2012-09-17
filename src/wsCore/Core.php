<?php
namespace wsCore;

class Core
{
    /** @var null|self */
    private static $_container = NULL;

    /** @var array      set easy mode */
    public static $easy = array(
        'DbAccess'  => '\wsCore\DbAccess\DbAccess',
        'Validator' => '\wsCore\Validator\Validator',
        'DataIO'    => '\wsCore\Validator\DataIO',
        '' => '',
    );

    /** @var array      set development mode */
    public static $dev = array(
        '\wsCore\DbAccess\DbAccess'   => '\wsCore\Aspect\LogDba',
        '\wsCore\Validator\Validator' => '\wsCore\Aspect\LogValidator',
    );
    // +----------------------------------------------------------------------+
    /**
     *
     */
    public function __construct() {
    }

    /**
     * starts wsCore Framework
     * @static
     * @return Core
     */
    public static function go() {
        return ( static::$_container ) ?: static::$_container=new \wsCore\DiContainer\Dimplet();
    }

    /**
     * going easy mode. 
     */
    public static function goEasy() {
        self::go();
        self::_fill( self::$easy );
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
     */
    public static function get( $id ) {
        self::$_container->get( $id );
    }
    // +----------------------------------------------------------------------+
}