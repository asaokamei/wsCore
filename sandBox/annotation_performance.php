<?php

/*
 * アノテーションの速度測定テスト
 * 
 */

interface interfaceTest1 {}
interface interfaceTest2 {}

/**
 * @InjectConstructor nothing
 */
class small implements interfaceTest1, interfaceTest2 {
    /**
     * @Inject nothing
     */
    function __construct(){}
    function func01() {}
    function func02() {}
    function func03() {}
    function func04() {}
}

/**
 * @InjectConstructor nothing
 */
class medium implements interfaceTest1, interfaceTest2 {
    /**
     * @Inject nothing
     */
    function __construct(){}
    function func01() {}
    function func02() {}
    function func03() {}
    function func04() {}
    function func05() {}
    function func06() {}
    function func07() {}
    function func08() {}
    function func09() {}
    function func10() {}
    function func11() {}
    function func12() {}
    function func13() {}
    function func14() {}
    function func15() {}
    function func16() {}
    function func17() {}
    function func18() {}
    function func19() {}
    function func20() {}
    function func21() {}
    function func22() {}
    function func23() {}
    function func24() {}
    function func25() {}
    function func26() {}
    function func27() {}
    function func28() {}
    function func29() {}
    function func30() {}
    function func41() {}
    function func42() {}
    function func43() {}
    function func44() {}
    function func45() {}
    function func46() {}
    function func47() {}
    function func48() {}
    function func49() {}
    function func50() {}
}

class time {
    /** @var float */
    var $time;
    var $base=1;
    function get_micro() {
        return (float) microtime( TRUE );
    }
    function start() {
        $this->time = $this->get_micro();
    }
    function stamp( $words='', $standard=FALSE ) {
        $diff = $this->get_micro() - $this->time;
        $diff *= 1000;
        if( $standard ) $this->base = $diff;
        $diff = $diff / $this->base * 100;
        echo sprintf( "$words: %.2f %%\n", $diff );
    }
}

$repeat = 10000;
test_annot( 'small', $repeat );
test_annot( 'medium', $repeat );

function test_annot( $class, $repeat )
{
    echo "\nTest Using `$class` Class repeating $repeat times\n\n";
    
    $time = new time();
    
    // instance with new 

    $time->start();
    for( $i = 0; $i < $repeat; $i++ ) {
        $obj = new small();
        unset( $obj );
    }
    $time->stamp( 'Simple Object', TRUE );

    // check all interfaces

    $time->start();
    for( $i = 0; $i < $repeat; $i++ ) {
        $ifc = class_implements( $class );
        unset( $ifc );
    }
    $time->stamp( 'Get Interfaces' );

    // create only reflection class

    $time->start();
    for( $i = 0; $i < $repeat; $i++ ) {
        $ref = new ReflectionClass( $class );
        unset( $ref );
    }
    $time->stamp( 'ReflectionClass' );

    // instance using reflection

    $time->start();
    for( $i = 0; $i < $repeat; $i++ ) {
        $ref = new ReflectionClass( $class );
        $obj = $ref->newInstance(); // ++++++
        unset( $ref );
        unset( $obj );
    }
    $time->stamp( 'Reflect newInstance' );

    // get phpDocs

    $time->start();
    for( $i = 0; $i < $repeat; $i++ ) {
        $ref = new ReflectionClass( $class );
        $con = $ref->getConstructor(); // ++++++
        $doc = $con->getDocComment(); // ++++++
        $obj = $ref->newInstance();
        unset( $ref );
        unset( $con );
        unset( $doc );
        unset( $obj );
    }
    $time->stamp( 'Reflect getConst&Doc' );

    // instance using reflection

    $time->start();
    for( $i = 0; $i < $repeat; $i++ ) {
        $ref = new ReflectionClass( $class );
        $con = $ref->getConstructor();
        $doc = $con->getDocComment();
        $all = $ref->getMethods(); // ++++++
        $obj = $ref->newInstance();
        unset( $ref );
        unset( $all );
        unset( $obj );
    }
    $time->stamp( 'Reflect getMethods' );

    // instance using reflection

    $time->start();
    for( $i = 0; $i < $repeat; $i++ ) {
        $ref = new ReflectionClass( $class );
        $all = $ref->getMethods();
        foreach( $all as $con ) {
            $doc = $con->getDocComment(); // ++++++
        }
        $obj = $ref->newInstance();
        unset( $ref );
        unset( $obj );
        unset( $all );
        unset( $con );
        unset( $doc );
    }
    $time->stamp( 'Reflect getMethods & Doc' );


}
