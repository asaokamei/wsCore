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
    // +----------------------------------------------------------------------+
    public function test_1()
    {
        $pdo = \wsCore\Dba\Rdb::connect( 'config' );
    }
    // +----------------------------------------------------------------------+
    /**
     * @expectedException PDOException
     */
    public function test_db_bad_connect()
    {
        $badDsn = array(
            'dsn' => 'db=noDb dbname=test username=admin password=admin'
        );
        \wsCore\Dba\Rdb::set( 'badDsn', $badDsn );
        $pdo = \wsCore\Dba\Rdb::connect( 'badDsn' );
    }
    // +----------------------------------------------------------------------+
}