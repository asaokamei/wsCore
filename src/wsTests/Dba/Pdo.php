<?php
namespace wsTests;

require_once( __DIR__ . '/../autoloader.php' );

class mockPdo
{
    var $config;
    var $exec;
    function __construct() {
        $this->config = func_get_args();
    }
    function exec( $exec ) {
        $this->exec = $exec;
    }
}

class Dba_Pdo_Test extends \PHPUnit_Framework_TestCase
{
    var $mockPdo;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->mockPdo = '\wsTests\mockPdo';
    }
    // +----------------------------------------------------------------------+
    public function test_1()
    {
    }
    // +----------------------------------------------------------------------+
    public function test_config_with_dsn()
    {
        $name = 'pdoTest';
        $dsn  = array(
            'dsn'  => 'db=myTest dbname=my_test username=test_user',
            'exec' => 'SET NAMES UTF8',
        );
        \wsCore\Dba\Pdo::setPdoClass( $this->mockPdo );
        \wsCore\Dba\Pdo::set( $name, $dsn );
        $pdo = \wsCore\Dba\Pdo::connect( $name );

        //var_dump( $pdo->config );
        $this->assertEquals( 'myTest:dbname=my_test; ', $pdo->config[0] );
        $this->assertEquals( 'test_user', $pdo->config[1] );
        $this->assertEquals( 'SET NAMES UTF8', $pdo->exec );
    }
    // +----------------------------------------------------------------------+
    public function test_construct_config()
    {
        $name = 'pdoTest';
        $dsn  = 'db=myTest dbname=my_test username=test_user';
        \wsCore\Dba\Pdo::setPdoClass( $this->mockPdo );
        \wsCore\Dba\Pdo::set( $name, $dsn );
        $pdo = \wsCore\Dba\Pdo::connect( $name );

        //var_dump( $pdo->config );
        //$this->assertEquals( '', $pdo->exec );
        $this->assertEquals( 'myTest:dbname=my_test; ', $pdo->config[0] );
        $this->assertEquals( 'test_user', $pdo->config[1] );
        //echo $pdo->exec;
        //echo 'hi';

    }
}

