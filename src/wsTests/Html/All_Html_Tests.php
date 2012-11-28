<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

class Html_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for WScore\'s Html' );
        $folder = __DIR__ . '/';
        $suite->addTestFile( $folder . 'Tags_Test.php' );
        $suite->addTestFile( $folder . 'Form_Test.php' );
        //$suite->addTestFile( $folder . 'Selector_Test.php' );

        return $suite;
    }
}
