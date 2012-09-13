<?php
namespace wsCore;

class Core extends \wsCore\DiContainer\Dimplet
{
    /** @var null|self */
    private static $_self = NULL;

    // +----------------------------------------------------------------------+
    /**
     *
     */
    public function __construct() {
    }

    /**
     * @static
     * @return Core
     */
    public static function core() {
        return ( static::$_self ) ?: static::$_self=new static();
    }

    /**
     * @static
     *
     */
    public static function clear() {
        static::$_self = NULL;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function __get( $id ) {
        return $this->get( $id );
    }
    // +----------------------------------------------------------------------+
}