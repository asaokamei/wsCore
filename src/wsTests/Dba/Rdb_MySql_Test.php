<?php
namespace wsTests\Dba;

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
        \wsCore\Dba\Rdb::set( 'config', $this->config );
    }
    public function test_1()
    {
    }
    // +----------------------------------------------------------------------+
    /**
     * @expectedException PDOException
     */
    public function test_bad_sql_statement()
    {
        $pdo = \wsCore\Dba\Rdb::connect( 'config' );
        $test = "CREATE TABLE test ( id int ) is a bad sql ;";
        $pdo->query( $test );
    }
    // +----------------------------------------------------------------------+
    public function test_connection_to_wsCore_db()
    {
        // should not throw any exceptions.
        $pdo = \wsCore\Dba\Rdb::connect( 'config' );
    }
    // +----------------------------------------------------------------------+
    public function test_mysql_driver_name()
    {
        $pdo = \wsCore\Dba\Rdb::connect( 'config' );
        $db  = $pdo->getAttribute( \PDO::ATTR_DRIVER_NAME );
        $this->assertEquals( 'mysql', $db );
    }
    // +----------------------------------------------------------------------+
    /**
     * @expectedException PDOException
     */
    public function test_bad_database_connection()
    {
        $badDsn = array(
            'dsn' => 'db=noDb dbname=test username=admin password=admin'
        );
        \wsCore\Dba\Rdb::set( 'badDsn', $badDsn );
        $pdo = \wsCore\Dba\Rdb::connect( 'badDsn' );
    }
    // +----------------------------------------------------------------------+
}