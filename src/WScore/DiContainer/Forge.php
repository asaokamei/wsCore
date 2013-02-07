<?php
namespace WScore\DiContainer;

class Forge
{
    private $useApc = true;
    private $cacheKey = 'DimForge:cached';
    private $cached = array();
    // +----------------------------------------------------------------------+
    public function __construct()
    {
        if( $this->useApc && function_exists( 'apc_store' ) ) {
            $this->useApc = true;
            $this->cached = unserialize( apc_fetch( $this->cacheKey ) ) ?: array();
        }
        else {
            $this->useApc = false;
        }
    }

    /**
     * list dependencies of a className. 
     * 
     * @param string $className
     * @return array
     */
    public function listDi( $className )
    {
        if( $diList = $this->fetch( $className ) ) return $diList;
        $refClass   = new \ReflectionClass( $className );
        $dimConst   = $this->dimConstructor( $refClass );
        $diList     = array(
            'construct' => $dimConst,
            'setter' => array(),
            'property' => array(),
        );
        $this->store( $className, $diList );
        return $diList;
    }

    /**
     * construct/forge a className injecting dependencies in $di.
     * 
     * @param $className
     * @param $di
     * @return object
     */
    public function forge( $className, $di )
    {
        $refClass   = new \ReflectionClass( $className );
        $args = $di[ 'construct' ];
        $object = $refClass->newInstanceArgs( $args );
        return $object;
    }
    // +----------------------------------------------------------------------+
    //  parsing PHPDoc for @DimInjection
    // +----------------------------------------------------------------------+

    /**
     * @param \ReflectionClass $refClass
     * @return array
     */
    private function dimConstructor( $refClass ) 
    {
        if( !$refConst   = $refClass->getConstructor() ) return array();
        if( !$comments   = $refConst->getDocComment()  ) return array();
        $injectList = Utils::parseDimDoc( $comments );
        return $injectList;
    }
    // +----------------------------------------------------------------------+
    //  Caching using APC.
    // +----------------------------------------------------------------------+
    /**
     * @param $className
     * @param $diList
     */
    private function store( $className, $diList ) 
    {
        $this->cached[ $className ] = $diList;
        apc_store( $this->cacheKey, serialize( $this->cached ) );
    }

    /**
     * @param $className
     * @return bool
     */
    private function fetch( $className )
    {
        if( !$this->useApc ) return false;
        if( array_key_exists( $className, $this->cached ) ) {
            return $this->cached[ $className ];
        }
        return false;
    }
    // +----------------------------------------------------------------------+
}