<?php
require_once( 'Aspect.php' );

class test
{
    function showIt( $arg ) {
        echo $arg . "\n";
        return '-> shown: '.$arg . "\n";
    }
}

// simple test

$test = new test;
echo $test->showIt( 'test' );

// intercept test

$test2 = new AopInterceptor( $test );
echo $test2->showIt( 'test from interceptor' );

// write a logger adviser.
class logger implements AopAdviserInterface
{
    public function invoke( $joinPoint, $args, $invoke, $returned ) {
        echo "--Logged: ". $args[0] . "\n";
    }
}
$log = new logger();

// construct container.
$aop = new AopContainer();
$aop->setAdviser( 'logger', $log ); // set adviser.
$aop->setJoinPoint( array( 'test', 'showIt', 'after' ), 'logger' );

$test2->injectAopContainer( $aop );

echo $test2->showIt( 'test with AOP' );
