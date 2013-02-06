<?php
namespace WScore\DiContainer;

class Forge
{
    public function __construct()
    {
        
    }

    /**
     * list dependencies of a className. 
     * 
     * @param string $className
     * @return array
     */
    public function listDi( $className )
    {
        $refClass   = new \ReflectionClass( $className );
        $dimConst   = $this->dimConstructor( $refClass );
        $diList     = array(
            'construct' => $dimConst,
            'setter' => array(),
            'property' => array(),
        );
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

    /**
     * @param \ReflectionClass $refClass
     * @return array
     */
    private function dimConstructor( $refClass ) 
    {
        if( !$refConst   = $refClass->getConstructor() ) return array();
        if( !$comments   = $refConst->getDocComment()  ) return array();
        $injectList = $this->parseDimDoc( $comments );
        return $injectList;
    }
    
    /**
     * parse phpDoc comments for DimInjection.
     *
     * @param string $comments
     * @param array  $injectInfo
     * @return array
     */
    private function parseDimDoc( $comments, $injectInfo=array() )
    {
        if( !preg_match_all( "/(@.*)$/mU", $comments, $matches ) ) return array();
        $injectList = array();
        foreach( $matches[1] as $comment ) {
            if( !preg_match( '/@DimInjection[ \t]+(.*)$/', $comment, $comMatch ) ) continue;
            $dimInfo = preg_split( '/[ \t]+/', trim( $comMatch[1] ) );
            $injectList[] = $this->parseDimInjection( $dimInfo, $injectInfo );
        }
        return $injectList;
    }

    /**
     * parse @DimInjection comment into injection information.
     * @param array $dimInfo
     * @param array $injectInfo
     * @return array
     */
    private function parseDimInjection( $dimInfo, $injectInfo=array() )
    {
        if( empty( $injectInfo ) ) {
            $injectInfo = array(
                'by' => 'fresh',
                'ob' => 'obj',
                'id' => null,
            );
        }
        foreach( $dimInfo as $info ) {
            switch( strtolower( $info ) ) {
                case 'none':   $injectInfo[ 'by' ] = null;      break;
                case 'get':    $injectInfo[ 'by' ] = 'get';     break;
                case 'fresh':  $injectInfo[ 'by' ] = 'fresh';   break;
                case 'raw':    $injectInfo[ 'ob' ] = 'raw';     break;
                case 'obj':    $injectInfo[ 'ob' ] = 'obj';     break;
                default:       $injectInfo[ 'id' ] = $info;     break;
            }
        }
        return $injectInfo;
    }
}