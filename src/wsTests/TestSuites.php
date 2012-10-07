<?php

/**
 * TODO: does not work since some test/class contains closure, generates serialize error. 
 */
class wsCore_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for wsCore\'s' );
        $folder = __DIR__ . '/';
        $suite->addTestFile( $folder . 'DbAccess/DbAccess_SuiteTest.php' );
        //$suite->addTestFile( $folder . 'DiContainer/Dimple_Test.php' );
        $suite->addTestFile( $folder . 'Html/Form_Test.php' );
        $suite->addTestFile( $folder . 'Html/Selector_Test.php' );
        $suite->addTestFile( $folder . 'Html/Tags_Test.php' );
        return $suite;
    }
}
