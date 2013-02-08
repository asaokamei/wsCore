<?php
namespace WScore\DiContainer;

class Forge
{
    public static $PROPERTY_INJECTION = false;

    /** @var Cache_Interface */
    private $cache  = null;
    // +----------------------------------------------------------------------+
    /**
     * @param null|Cache_Interface $cache
     */
    public function __construct( $cache=null )
    {
        if( $cache ) {
            $this->cache = $cache;
        } else {
            $this->cache = Cache::getCache();
        }
    }

    /**
     * DI by constructor. uses annotation
     *
     * @DimInjection
     *
     * @param Dimplet    $container
     * @param string     $className
     * @param array|null $option
     * @return object
     */
    public function construct( $container, $className, $option=array() )
    {
        $injectList = $this->listDi( $className );
        $injectList = Utils::mergeOption( $injectList, $option );
        $diList = array(
            'construct' => array(),
            'property'  => array(),
            'setter'    => array(),
        );
        foreach( $injectList['construct'] as $key => $injectInfo ) {
            $diList['construct'][$key] = Utils::constructByInfo( $container, $injectInfo );
        }
        foreach( $injectList['property'] as $key => $injectInfo ) {
            $diList['property'][$key][0] = Utils::constructByInfo( $container, $injectInfo[0] );
            $diList['property'][$key][1] = $injectInfo[1];
        }
        $object = $this->forge( $className, $diList );
        return $object;
    }

    /**
     * list dependencies of a className. 
     * 
     * @param string $className
     * @return array
     */
    public function listDi( $className )
    {
        if( $diList = $this->cache->fetch( $className ) ) return $diList;
        $refClass   = new \ReflectionClass( $className );
        $dimConst   = $this->dimConstructor( $refClass );
        $dimProp    = $this->dimProperty( $refClass );
        $diList     = array(
            'construct' => $dimConst,
            'setter'    => array(),
            'property'  => $dimProp,
        );
        $this->cache->store( $className, $diList );
        return $diList;
    }

    /**
     * construct/forge a className injecting dependencies in $di.
     * 
     * @param $className
     * @param $diList
     * @return object
     */
    public function forge( $className, $diList )
    {
        $refClass   = new \ReflectionClass( $className );
        // constructor injection
        $object = $refClass->newInstanceArgs( $diList[ 'construct' ] );
        // property injection.
        foreach( $diList[ 'property' ] as $propName => $dep ) 
        {
            if( !$refClass->hasProperty( $propName ) ) continue;
            /** @var $refProp \ReflectionProperty */
            $refProp = $dep[1];
            $refProp->setAccessible( true );
            $refProp->setValue( $object, $dep[0] );
        }
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

    /**
     * get dependency information of properties for a class.
     * searches all properties in parent classes as well.
     *
     * @param \ReflectionClass $refClass
     * @return array
     */
    public  function dimProperty( $refClass )
    {
        $injectList = array();
        if( !self::$PROPERTY_INJECTION ) return $injectList;
        do {
            if( $properties = $refClass->getProperties() ) {
                foreach( $properties as $refProp ) {
                    if( isset( $injectList[ $refProp->name ] ) ) continue;
                    if( $comments = $refProp->getDocComment() ) {
                        if( $info = Utils::parseDimDoc( $comments ) ) {
                            $injectList[ $refProp->name ] = array( end( $info ), $refProp );
                        }
                    }
                }
            }
            $refClass = $refClass->getParentClass();
        } while( false !== $refClass );
        return $injectList;
    }
    // +----------------------------------------------------------------------+
}