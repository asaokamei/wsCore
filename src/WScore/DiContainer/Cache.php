<?php
namespace WScore\DiContainer;

class Cache
{
    private $useApc = false;
    private $cached = array();
    private $header = 'DimCache:';

    /**
     * checks what cache to use.
     */
    public function __construct()
    {
        if( function_exists( 'apc_store' ) ) {
            $this->useApc = true;
        }
    }

    /**
     * stores objects.
     *
     * @param $className
     * @param $value
     */
    public function store( $className, $value )
    {
        $className = $this->header . str_replace( '\\', '-', $className );
        if( $this->useApc ) {
            try {
                apc_store( $className, $value );
            } catch( \Exception $e ) {
                apc_delete( $className );
            }
        } else {
            $this->cached[ $className ] = $value;
        }
    }

    /**
     * fetches objects from cache.
     *
     * @param $className
     * @return bool|mixed
     */
    public function fetch( $className )
    {
        $className = $this->header . str_replace( '\\', '-', $className );
        if( $this->useApc ) {
            try {
                $fetched = apc_fetch( $className );
            } catch( \Exception $e ) {
                echo $className;
                echo $e->getMessage();
                exit;
            }
        } else {
            $fetched = array_key_exists( $className, $this->cached ) ? $this->cached[ $className ]: false;
        }
        return $fetched;
    }
}