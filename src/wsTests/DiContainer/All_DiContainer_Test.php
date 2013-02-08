<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

class DiContainer_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for WScore\'s DiContainer' );
        $folder = __DIR__ . '/';
        $suite->addTestFile( $folder . 'Utils_Test.php' );
        $suite->addTestFile( $folder . 'Property_Test.php' );
        $suite->addTestFile( $folder . 'Dimple_Test.php' );

        return $suite;
    }
}
