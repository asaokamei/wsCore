<?php
namespace WScore\DiContainer;

class DimConstructor
{
    /**
     * gets constructor document for DimInjection.
     * 
     * @param \ReflectionClass $refClass
     * @return array
     */
    public function getList( $refClass )
    {
        $refConst   = $refClass->getConstructor();
        if( !$refConst ) return array();
        $comments   = $refConst->getDocComment();
        if( empty( $comments ) ) return array();
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
                'id' => NULL,
            );
        }
        foreach( $dimInfo as $info ) {
            switch( strtolower( $info ) ) {
                case 'none':   $injectInfo[ 'by' ] = NULL;      break;
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