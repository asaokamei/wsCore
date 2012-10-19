<?php

class DbAccess_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for wsCore\'s DbAccess' );
        $folder = dirname( __FILE__ ) . '/';
        $suite->addTestFile( $folder . 'PdObject_Test.php' );
        $suite->addTestFile( $folder . 'Rdb_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Rdb_Test.php' );
        $suite->addTestFile( $folder . 'Query_Test.php' );
        $suite->addTestFile( $folder . 'Dao_MySql_Test.php' );
        $suite->addTestFile( $folder . 'DataRecord_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Relation_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Relation_HasJoined_MySql_Test.php' );
        $suite->addTestFile( $folder . 'Relation_HasJoinDao_MySql_Test.php' );
        return $suite;
    }
}
?>