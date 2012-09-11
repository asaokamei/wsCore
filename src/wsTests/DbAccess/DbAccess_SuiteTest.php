<?php

class DbAccess_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for wsCore\'s DbAccess' );
        $folder = dirname( __FILE__ ) . '/';
        $suite->addTestFile( $folder . 'Dba_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Rdb_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Rdb_Test.php' );
        $suite->addTestFile( $folder . 'Sql_Test.php' );
        return $suite;
    }
}
?>