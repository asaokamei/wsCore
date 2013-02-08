<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

class All_DataMapper_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for WScore\'s DataMapper' );
        $folder = dirname( __FILE__ ) . '/';
        $suite->addTestFile( $folder . 'EntityManager.php' );
        $suite->addTestFile( $folder . 'EntityManagerMySql.php' );
        return $suite;
    }
}

