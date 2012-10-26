<?php
namespace wsTests\DbAccess;
use \wsCore\DbAccess\Rdb as Rdb;

require_once( __DIR__ . '/../../autoloader.php' );

class Dba_Rdb_Test extends \PHPUnit_Framework_TestCase
{
    var $mockPdo;
    /** @var \wsCore\DbAccess\Rdb */
    var $rdb;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->mockPdo = '\wsTests\DbAccess\Mock_RdbPdo';
        $this->rdb     = new Rdb();
        $this->rdb->pdoClass = $this->mockPdo;
    }

    // +----------------------------------------------------------------------+
    public function test_connect_with_new()
    {
        $db = 'Test';
        $dbname = 'test1';
        $name1 = "db={$db} dbname={$dbname}";
        $dsn   = "{$db}:dbname={$dbname};";
        /** @var $pdo1 \wsTests\DbAccess\Mock_RdbPdo */
        $pdo1 = $this->rdb->connect( $name1 );
        $this->assertEquals( $dsn, $pdo1->config[0] );
    }
    /**
     * @expectedException \RuntimeException
     */
    public function test_name_not_set()
    {
        $this->rdb->connect( NULL );
    }
    // +----------------------------------------------------------------------+
    public function test_config_with_dsn()
    {
        $dsn  = array(
            'dsn'  => 'db=myTest dbname=my_test',
            'exec' => 'SET NAMES UTF8',
            'username' => 'test_user',
            'password' => 'testPswd',
        );
        /** @var $pdo \wsTests\DbAccess\Mock_RdbPdo */
        $pdo = $this->rdb->connect( $dsn );

        $this->assertEquals( $dsn['dsn'], $pdo->config[0] );
        $this->assertEquals( 'test_user', $pdo->config[1] );
        $this->assertEquals( 'testPswd', $pdo->config[2] );
        $this->assertEquals( 'SET NAMES UTF8', $pdo->exec );
    }
    // +----------------------------------------------------------------------+
    public function test_construct_config()
    {
        $dsn  = 'db=myTest dbname=my_test';
        /** @var $pdo \wsTests\DbAccess\Mock_RdbPdo */
        $pdo = $this->rdb->connect( $dsn );

        $this->assertEquals( 'myTest:dbname=my_test;', $pdo->config[0] );

    }
}

