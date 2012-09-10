<?php
namespace wsTests\Dba;

require_once( __DIR__ . '/../../autoloader.php' );

class Dba_Dba_MySql_Test extends \PHPUnit_Framework_TestCase
{
    var $config = array();
    /** @var \wsCore\Dba\Dba */
    var $dba = NULL;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->config = array(
            'dsn' => 'db=mysql dbname=test_wsCore username=admin password=admin'
        );
        \wsCore\Dba\Rdb::set( 'config', $this->config );
        $this->dba = new \wsCore\Dba\Dba();
    }
    // +----------------------------------------------------------------------+
    public function test_1()
    {
        $this->dba->query( 'DROP TABLE IF EXISTS test;' );
        $this->dba->query( 'CREATE TABLE test ( id int );' );
    }
    // +----------------------------------------------------------------------+
}
