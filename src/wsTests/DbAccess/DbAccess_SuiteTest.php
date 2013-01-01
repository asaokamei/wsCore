<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

class DbAccess_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for WScore\'s DbAccess' );
        $folder = dirname( __FILE__ ) . '/';
        //$suite->addTestFile( $folder . 'Rdb_Test.php' );
        $suite->addTestFile( $folder . 'Rdb_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Rdb_PgSql_Test.php' );
        $suite->addTestFile( $folder . 'Rdb_Sqlite_Test.php' );
        $suite->addTestFile( $folder . 'PdObject_Test.php' );
        $suite->addTestFile( $folder . 'PdObject_PgSql_Test.php' );
        $suite->addTestFile( $folder . 'Query_Test.php' );
        $suite->addTestFile( $folder . 'Query_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Query_PgSql_Test.php' );
        $suite->addTestFile( $folder . 'Query_PgSql_Quoted_Test.php' );
        $suite->addTestFile( $folder . 'Dao_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Relation_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Relation_HasJoined_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Relation_HasJoinDao_MySql_Test.php' );
        return $suite;
    }
}

