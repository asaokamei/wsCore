<?php

class wsCore_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for wsCore\'s' );
        $folder = __DIR__ . '/';

        // Validator Tests
        $suite->addTestFile( $folder . 'Validator/Validator_Test.php' );
        $suite->addTestFile( $folder . 'Validator/DataIO_Test.php' );
        
        // wait until Dimplet test rewrite. 
        //$suite->addTestFile( $folder . 'DiContainer/Dimple_Test.php' );
        
        // Html Tests
        $suite->addTestFile( $folder . 'Html/Form_Test.php' );
        $suite->addTestFile( $folder . 'Html/Tags_Test.php' );
        // wait until Selector test uses PHPUnit. 
        //$suite->addTestFile( $folder . 'Html/Selector_Test.php' );

        // DbAccess Tests
        $suite->addTestFile( $folder . 'DbAccess/DbAccess_SuiteTest.php' );
        $suite->addTestFile( $folder . 'DataMapper/EntityManager.php' );
        $suite->addTestFile( $folder . 'DataMapper/EntityManagerMySql.php' );

        return $suite;
    }
}
