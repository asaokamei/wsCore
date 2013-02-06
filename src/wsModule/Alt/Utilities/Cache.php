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
    public static function initialize()
    {
        if( function_exists( 'apc_store' ) ) {
            self::$useApc = true;
        }
        self::$useApc = false;
    }

    /**
     * @param string $className
     * @param string $id
     * @return string
     */
    private static function name( $className, $id ) {
        return self::$header . $id . ':' . str_replace( '\\', '-', $className );
    }
    /**
     * stores objects.
     *
     * @param $className
     * @param $id
     * @param $value
     */
    public static function store( $className, $value, $id=null )
    {
        $className = self::name( $className, $id );
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
     * @param $id
     * @return bool|mixed
     */
    public static function fetch( $className, $id=null )
    {
        $className = self::name( $className, $id );
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