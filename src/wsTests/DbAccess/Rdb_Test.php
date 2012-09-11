<?php
namespace wsTests\Dba;
use \wsCore\DbAccess\Rdb as Rdb;

require_once( __DIR__ . '/../../autoloader.php' );

class Dba_Rdb_Test extends \PHPUnit_Framework_TestCase
{
    var $mockPdo;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->mockPdo = '\wsTests\DbAccess\RdbMockPdo';
        Rdb::_init();
    }

    // +----------------------------------------------------------------------+
    public function test_connect_with_new()
    {
        $name1 = 'pdoTest1';
        $dsn1   = array(
            'dsn' => 'db=myTest dbname=test1'
        );
        Rdb::setPdoClass( $this->mockPdo );
        Rdb::set( $name1, $dsn1 );
        $pdo1 = Rdb::connect( $name1 );
        $pdo2 = Rdb::connect( $name1, TRUE );
        $this->assertEquals( $pdo1, $pdo2 );
        $this->assertNotSame( $pdo1, $pdo2 );
    }
    // +----------------------------------------------------------------------+
    /**
     * @expectedException RuntimeException
     */
    public function test_name_not_exists()
    {
        Rdb::setPdoClass( $this->mockPdo );
        // setting test1 name
        $name1 = 'pdoTest1';
        $dsn1   = array(
            'dsn' => 'db=myTest dbname=test1'
        );
        Rdb::set( $name1, $dsn1 );
        // getting name that does not exist
        $name_not_exist = 'must_throw_exception';
        $pdo = Rdb::connect( $name_not_exist );
    }
    /**
     * @expectedException RuntimeException
     */
    public function test_name_not_set()
    {
        $pdo = Rdb::connect();
    }
    // +----------------------------------------------------------------------+
    public function test_connect_to_various_names()
    {
        Rdb::setPdoClass( $this->mockPdo );
        // setting test1 name
        $name1 = 'pdoTest1';
        $dsn1   = array(
            'dsn' => 'db=myTest dbname=test1'
        );
        Rdb::set( $name1, $dsn1 );
        
        // setting test2 name
        $name2 = 'pdoTEST2';
        $dsn2   = array(
            'dsn' => 'db=myName dbname=TEST2'
        );
        Rdb::set( $name2, $dsn2 );
        
        // should connect to the default, i.e. the first name.
        /** @var $pdo \wsTests\Dba\RdbMockPdo */
        $pdo = Rdb::connect();
        $this->assertEquals( 'myTest:dbname=test1; ', $pdo->config[0] );

        // should connect to the name2
        $pdo = Rdb::connect( $name2 );
        $this->assertEquals( 'myName:dbname=TEST2; ', $pdo->config[0] );

        // make sure connects to default, i.e. name1
        $pdo = Rdb::connect();
        $this->assertEquals( 'myTest:dbname=test1; ', $pdo->config[0] );

        // change default to name2. 
        Rdb::useConfig( $name2 );
        $pdo = Rdb::connect();
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
        Rdb::setPdoClass( $this->mockPdo );
        Rdb::set( $name, $dsn );
        /** @var $pdo \wsTests\Dba\RdbMockPdo */
        $pdo = Rdb::connect( $name );

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
        Rdb::setPdoClass( $this->mockPdo );
        Rdb::set( $name, $dsn );
        /** @var $pdo \wsTests\Dba\RdbMockPdo */
        $pdo = Rdb::connect( $name );

        $this->assertEquals( 'myTest:dbname=my_test; ', $pdo->config[0] );

    }
}

