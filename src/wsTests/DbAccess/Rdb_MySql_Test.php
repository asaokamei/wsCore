<?php
namespace wsTests\Dba;
use \wsCore\DbAccess\Rdb as Rdb;

require_once( __DIR__ . '/../../autoloader.php' );

class Dba_Rdb_MySql_Test extends \PHPUnit_Framework_TestCase
{
    var $config = array();
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->config = array(
            'dsn' => 'db=mysql dbname=test_wsCore username=admin password=admin'
        );
        Rdb::set( 'config', $this->config );
    }
    // +----------------------------------------------------------------------+
    public function test_returning_2_pdo()
    {
        Rdb::set( 'con2nd', $this->config );
        $pdo1 = Rdb::connect( 'config' );
        $pdo2 = Rdb::connect( 'con2nd' );

        // two PDO has the same attributes
        $this->assertEquals( $pdo1, $pdo2 );
        // but references different PDO object.
        $this->assertNotSame( $pdo1, $pdo2 );
    }
    /**
     * @expectedException PDOException
     */
    public function test_bad_sql_statement()
    {
        $pdo = Rdb::connect( 'config' );
        $test = "CREATE TABLE test ( id int ) is a bad sql ;";
        $pdo->query( $test );
    }

    /**
     *
     */
    public function test_connection_to_wsCore_db()
    {
        // should not throw any exceptions.
        $pdo = Rdb::connect( 'config' );
    }

    /**
     *
     */
    public function test_mysql_driver_name()
    {
        $pdo = Rdb::connect( 'config' );
        $db  = $pdo->getAttribute( \PDO::ATTR_DRIVER_NAME );
        $this->assertEquals( 'mysql', $db );
    }

    /**
     * @expectedException PDOException
     */
    public function test_bad_database_connection()
    {
        $badDsn = array(
            'dsn' => 'db=noDb dbname=test username=admin password=admin'
        );
        Rdb::set( 'badDsn', $badDsn );
        $pdo = Rdb::connect( 'badDsn' );
    }
    // +----------------------------------------------------------------------+
}