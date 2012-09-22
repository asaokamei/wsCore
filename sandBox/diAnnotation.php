<?php

// it is easy to use phpDocument by getDocComment.

// think about Constructor injection.

interface InterfaceDInjectionSample {}

class Sample
{
    protected $service;

    /**
     * @param Service $service
     * @inject
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
     * @DInjection Service
     */
    public function __construct( $service ) {
        $this->service = $service;
    }
    /**
     * @param $service
     * @DInjection none
     */
    public function setService( $service ) {
        $this->service = $service;
    }
}

showDocs( 'Sample' );
showDocs( 'Sample2' );

function showDocs( $className ) {
    $refClass  = new ReflectionClass( $className );
    $refConst  = $refClass->getConstructor();
    $comments  = $refConst->getDocComment();
    echo "class: $className \n $comments \n\n";
}

