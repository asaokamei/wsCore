<?php

class WScore_SuiteTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite( 'all tests for WScore\'s' );
        $folder = __DIR__ . '/';

        // Validator Tests
        $suite->addTestFile( $folder . 'Validation/All_Validation_Tests.php' );
        
        // wait until Dimplet test rewrite. 
        $suite->addTestFile( $folder . 'DiContainer/Dimple_Test.php' );
        
        // Html Tests
        $suite->addTestFile( $folder . 'Html/Form_Test.php' );
        $suite->addTestFile( $folder . 'Html/Tags_Test.php' );
        // wait until Selector test uses PHPUnit. 
        //$suite->addTestFile( $folder . 'Html/Selector_Test.php' );

        // DbAccess Tests
        $suite->addTestFile( $folder . 'DbAccess/DbAccess_SuiteTest.php' );
        $suite->addTestFile( $folder . 'DataMapper/All_DataMapper_SuiteTest.php' );

        return $suite;
    }
}
