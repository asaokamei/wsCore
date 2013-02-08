<?php
namespace WScore\DiContainer;

class Cache_Apc implements Cache_Interface
{
    private $cacheKey = 'WScore:Cache';
    private $cached = array();
    // +----------------------------------------------------------------------+
    //  Caching using APC.
    // +----------------------------------------------------------------------+
    /**
     *
     */
    public function __construct()
    {
        if( function_exists( 'apc_store' ) ) {
            $this->cached = unserialize( apc_fetch( $this->cacheKey ) ) ?: array();
        }
        else {
            throw new \RuntimeException( 'apc function not available.' );
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public function store( $name, $value )
    {
        $this->cached[ $name ] = $value;
        apc_store( $this->cacheKey, serialize( $this->cached ) );
    }

    /**
     * @param $name
     * @return bool
     */
    public function fetch( $name )
    {
        if( array_key_exists( $name, $this->cached ) ) {
            return $this->cached[ $name ];
        }
        return false;
    }
    // +----------------------------------------------------------------------+
}