<?php
namespace wsTests\Dba;

require_once( __DIR__ . '/../../autoloader.php' );

class Dba_Rdb_Test extends \PHPUnit_Framework_TestCase
{
    var $mockPdo;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->mockPdo = '\wsTests\Dba\RdbMockPdo';
    }

    /**
     * expectedException RuntimeException
     */
    // +----------------------------------------------------------------------+
    public function test_name_not_exists()
    {
        \wsCore\Dba\Rdb::setPdoClass( $this->mockPdo );
        // setting test1 name
        $name1 = 'pdoTest1';
        $dsn1   = array(
            'dsn' => 'db=myTest dbname=test1'
        );
        \wsCore\Dba\Rdb::set( $name1, $dsn1 );
        // getting name that does not exist
        $name_not_exist = 'must_throw_exception';
        //$pdo = \wsCore\Dba\Rdb::connect( $name_not_exist );
    }
    /**
     * @expectedException RuntimeException
     */
    // +----------------------------------------------------------------------+
    public function test_name_not_set()
    {
        $pdo = \wsCore\Dba\Rdb::connect();
    }
    // +----------------------------------------------------------------------+
    public function test_connect_to_various_names()
    {
        \wsCore\Dba\Rdb::setPdoClass( $this->mockPdo );
        // setting test1 name
        $name1 = 'pdoTest1';
        $dsn1   = array(
            'dsn' => 'db=myTest dbname=test1'
        );
        \wsCore\Dba\Rdb::set( $name1, $dsn1 );
        
        // setting test2 name
        $name2 = 'pdoTEST2';
        $dsn2   = array(
            'dsn' => 'db=myName dbname=TEST2'
        );
        \wsCore\Dba\Rdb::set( $name2, $dsn2 );
        
        // should connect to the default, i.e. the first name. 
        $pdo = \wsCore\Dba\Rdb::connect();
        $this->assertEquals( 'myTest:dbname=test1; ', $pdo->config[0] );

        // should connect to the name2
        $pdo = \wsCore\Dba\Rdb::connect( $name2 );
        $this->assertEquals( 'myName:dbname=TEST2; ', $pdo->config[0] );

        // make sure connects to default, i.e. name1
        $pdo = \wsCore\Dba\Rdb::connect();
        $this->assertEquals( 'myTest:dbname=test1; ', $pdo->config[0] );

        // change default to name2. 
        \wsCore\Dba\Rdb::useConfig( $name2 );
        $pdo = \wsCore\Dba\Rdb::connect();
        $this->assertEquals( 'myName:dbname=TEST2; ', $pdo->config[0] );
    }
    // +----------------------------------------------------------------------+
    public function test_config_with_dsn()
    {
        $name = 'pdoTest';
        $dsn  = array(
            'dsn'  => 'db=myTest dbname=my_test username=test_user password=testPswd',
            'exec' => 'SET NAMES UTF8',
        );
        \wsCore\Dba\Rdb::setPdoClass( $this->mockPdo );
        \wsCore\Dba\Rdb::set( $name, $dsn );
        $pdo = \wsCore\Dba\Rdb::connect( $name );

        $this->assertEquals( 'myTest:dbname=my_test; ', $pdo->config[0] );
        $this->assertEquals( 'test_user', $pdo->config[1] );
        $this->assertEquals( 'testPswd', $pdo->config[2] );
        $this->assertEquals( 'SET NAMES UTF8', $pdo->exec );
    }
    // +----------------------------------------------------------------------+
    public function test_construct_config()
    {
        $name = 'pdoTest';
        $dsn  = 'db=myTest dbname=my_test';
        \wsCore\Dba\Rdb::setPdoClass( $this->mockPdo );
        \wsCore\Dba\Rdb::set( $name, $dsn );
        $pdo = \wsCore\Dba\Rdb::connect( $name );

        $this->assertEquals( 'myTest:dbname=my_test; ', $pdo->config[0] );

    }
}

