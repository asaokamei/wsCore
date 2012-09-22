<?php

// it is easy to use phpDocument by getDocComment.

// think about Constructor injection.

interface InterfaceDInjectionSample {}

class Sample
{
    protected $service;

    /**
     * @param Sample $service
     * @DimInjection None Service
     * @DimInjection None 
     */
    public function __construct( $service ) {
        $this->service = $service;
    }

    /**
     * @param $service
     * @InterfaceInjection
     */
    public function setService( $service ) {
        $this->service = $service;
    }
}

class Sample2 extends Sample implements InterfaceDInjectionSample
{
    /**
     * @param $service
     * @DimInjection Fresh Raw Sample
     * @DimInjection Fresh Raw Sample None
     * @DimInjection Fresh Raw Sample baka
     */
    public function __construct( $service ) {
        $this->service = $service;
    }
    /**
     * @param $service
     * @DimInjection none
     */
    public function setService( $service ) {
        $this->service = $service;
    }
}

injectConstruct( 'Sample' );
injectConstruct( 'Sample2' );

function injectConstruct( $className ) 
{
    $refClass  = new ReflectionClass( $className );
    $refConst  = $refClass->getConstructor();
    $comments  = $refConst->getDocComment();
    $dimInfo   = parseDimDoc( $comments );
    //var_dump( $dimInfo );
    $dimInfo   = prepareDim( $dimInfo );
    var_dump( $dimInfo );
    //echo "class: $className \n $comments \n\n";
}

function prepareDim( $dimList ) 
{
    if( empty( $dimList ) ) return array();
    $dimInjection = array();
    foreach( $dimList as $dimInfo ) 
    {
        $dimInjection[] = doParse( $dimInfo );
    }
    return $dimInjection;
}

function doParse( $dimInfo, $injection=array() )
{
    if( empty( $injection ) ) {
        $injection = array(
            'by' => 'fresh',
            'ob' => 'obj',
            'id' => NULL,
        );
    }
    foreach( $dimInfo as $info ) {
        $info = strtolower( $info );
        switch( strtolower( $info ) ) {
            case 'none':   $injection[ 'by' ] = NULL;      break;
            case 'get':    $injection[ 'by' ] = 'get';     break;
            case 'fresh':  $injection[ 'by' ] = 'fresh';   break;
            case 'raw':    $injection[ 'ob' ] = 'raw';     break;
            case 'obj':    $injection[ 'ob' ] = 'obj';     break;
            default:       $injection[ 'id' ] = $info;     break;
        }
    }
    return $injection;    
}

function parseDimDoc( $comments ) 
{
    if( !preg_match_all( "/(@.*)$/mU", $comments, $matches ) ) return array();
    $dimList = array();
    foreach( $matches[1] as $comment ) {
        if( !preg_match( '/@DimInjection[ \t]+(.*)$/', $comment, $comMatch ) ) continue;
        $dimList[] = preg_split( '/[ \t]+/', trim( $comMatch[1] ) );
    }
    return $dimList;
}

