<?php
namespace WScore\DiContainer;

class DimConstructor
{
    private $useApc = false;
    private $cached = array();
    private $header = 'DimConstruct:';

    /**
     *
     */
    public function __construct()
    {
        if( function_exists( 'apc_store' ) ) {
            $this->useApc = true;
        }
    }
    /**
     * gets constructor document for DimInjection.
     * 
     * @param \ReflectionClass $refClass
     * @return array
     */
    public function getList( $refClass )
    {
        $refConst   = $refClass->getConstructor();
        $className  = $refClass->getName();
        if( !$refConst ) return array();
        if( $injectList = $this->fetch( $className ) ) {
            return $injectList;
        }
        $comments   = $refConst->getDocComment();
        if( empty( $comments ) ) return array();
        $injectList = $this->parseDimDoc( $comments );
        $this->store( $className, $injectList );
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
    private function store( $className, $value )
    {
        $className = $this->header . str_replace( '\\', '-', $className );
        if( $this->useApc ) {
            apc_store( $className, $value );
        } else {
            $this->cached[ $className ] = $value;
        }
    }
    private function fetch( $className )
    {
        $className = $this->header . str_replace( '\\', '-', $className );
        if( $this->useApc ) {
            $fetched = apc_fetch( $className );
        } else {
            $fetched = array_key_exists( $className, $this->cached ) ? $this->cached[ $className]: false;
        }
        return $fetched;
    }
}