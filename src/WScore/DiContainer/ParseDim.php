<?php
namespace WScore\DiContainer;

class ParseDim
{
    // +----------------------------------------------------------------------+
    //  parsing @DimInjection in PHPDoc.
    // +----------------------------------------------------------------------+

    /**
     * parse phpDoc comments for DimInjection.
     *
     * @param string $comments
     * @return array
     */
    public function parseDimDoc( $comments )
    {
        if( !preg_match_all( "/(@.*)$/mU", $comments, $matches ) ) return array();
        $injectList = array();
        foreach( $matches[1] as $comment ) {
            if( !preg_match( '/@DimInjection[ \t]+(.*)$/', $comment, $comMatch ) ) continue;
            $dimInfo = preg_split( '/[ \t]+/', trim( $comMatch[1] ) );
            $injectList[] = self::parseDimInjection( $dimInfo );
        }
        return $injectList;
    }

    /**
     * parse @DimInjection comment into injection information.
     *
     * @param array $dimInfo
     * @return array
     */
    private function parseDimInjection( $dimInfo )
    {
        $injectInfo = array(
            'by' => 'fresh',
            'ob' => 'obj',
            'id' => null,
        );
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

    /**
     * @param Dimplet  $container
     * @param array    $injectInfo
     * @return mixed|null
     */
    public function constructByInfo( $container, $injectInfo )
    {
        extract( $injectInfo ); // gets $by, $ob, and $id.
        /** @var $by string   type of object fresh/get   */
        /** @var $ob string   type of construct obj/raw  */
        /** @var $id string   look for id to generate    */
        $object = null;
        if( $by && $ob && $id ) {
            if( $ob == 'raw' ) {
                $object = $container->raw( $id, $by );
            }
            else {
                $object = $container->$by( $id );
            }
        }
        return $object;
    }

}