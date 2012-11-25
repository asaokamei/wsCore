<?php

    ini_set( 'display_errors', 1 );
    error_reporting( E_ALL );

class Validator_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for WScore\'s Validator' );
        $folder = __DIR__ . '/';
        $suite->addTestFile( $folder . 'Validator_Test.php' );
        $suite->addTestFile( $folder . 'Validate_test.php' );
        $suite->addTestFile( $folder . 'Rules_Test.php' );
        $suite->addTestFile( $folder . 'DataIO_Test.php' );

        return $suite;
    }
}
