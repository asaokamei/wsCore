<?php
namespace WScore\DiContainer;

class Cache
{
    private static $useApc = false;
    private static $cached = array();
    private static $header = 'DimCache:';

    /**
     * checks what cache to use.
     */
    public function initialize()
    {
        if( function_exists( 'apc_store' ) ) {
            self::$useApc = true;
        }
    }

    /**
     * stores objects.
     *
     * @param $className
     * @param $value
     */
    public static function store( $className, $value )
    {
        $className = self::$header . str_replace( '\\', '-', $className );
        if( self::$useApc ) {
            try {
                apc_store( $className, $value );
            } catch( \Exception $e ) {
                apc_delete( $className );
            }
        } else {
            self::$cached[ $className ] = $value;
        }
    }

    /**
     * fetches objects from cache.
     *
     * @param $className
     * @return bool|mixed
     */
    public static function fetch( $className )
    {
        $className = self::$header . str_replace( '\\', '-', $className );
        $fetched = false;
        if( self::$useApc ) {
            try {
                $fetched = apc_fetch( $className );
            } catch( \Exception $e ) {
                echo $className;
                echo $e->getMessage();
                exit;
            }
        }
        return $fetched;
    }
}