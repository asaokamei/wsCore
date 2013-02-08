<?php
namespace WScore\DiContainer;

class Cache
{
    public static $useMem = false;
    public static $useApc = true;
    // +----------------------------------------------------------------------+
    public static function getCache( $location=null )
    {
        $cache = 'None';
        if( self::$useMem && class_exists( 'Memcached' ) ) {
            $cache = 'Memcache';
        }
        elseif( self::$useApc && function_exists( 'apc_store' ) ) {
            $cache = 'Apc';
        }
        elseif( isset( $location ) ) {
            $cache = 'File';
        }
        $cache = '\WScore\DiContainer\Cache_' . $cache;
        if( !class_exists( $cache ) ) {
            throw new \RuntimeException( "no such cache: $cache" );
        }
        return new $cache();
    }
    // +----------------------------------------------------------------------+
}