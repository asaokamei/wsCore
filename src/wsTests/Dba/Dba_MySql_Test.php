<?php
namespace wsTests\Dba;

require_once( __DIR__ . '/../../autoloader.php' );

class Dba_Dba_MySql_Test extends \PHPUnit_Framework_TestCase
{
    var $config = array();
    /** @var \wsCore\Dba\Dba */
    var $dba = NULL;
    var $table = 'test_wsCore';
    var $column_list = '';
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->config = array(
            'dsn' => 'db=mysql dbname=test_wsCore username=admin password=admin'
        );
        \wsCore\Dba\Rdb::set( 'config', $this->config );
        $this->dba = new \wsCore\Dba\Dba();
        $this->column_list = '
            id int NOT NULL AUTO_INCREMENT,
            name CHAR(30),
            age  int,
            bdate date,
            no_null text NOT NULL,
            PRIMARY KEY (id)
        ';
        $this->setUp_TestTable();
    }

    /**
     * set up permanent tables for testing.
     * use this if you are testing the tests!
     */
    public function setUp_TestTable_perm()
    {
        $this->table = 'test_wsCorePerm';
        $this->setUp_TestTable();
    }

    /**
     * creates new table for testing.
     */
    public function setUp_TestTable()
    {
        $this->dba->query( "DROP TABLE IF EXISTS {$this->table};" );
        $this->dba->query( "
        CREATE TABLE {$this->table} ( {$this->column_list} );
        " );
    }
    // +----------------------------------------------------------------------+
    public function test_insert_using_prepare()
    {
        $prepare = "
            INSERT {$this->table}
                ( name, age, bdate, no_null )
            VALUES
                ( :name, :age, :bdate, :no_null );
        ";
        $values = array(
            ':name' => 'test prep',
            ':age' => '41',
            ':bdate' => '1980-02-03',
            ':no_null' => 'never null',
        );
        $this->dba->prepare( $prepare );
        $this->dba->execute( $values );
        $id1 = $this->dba->lastId();
        $this->assertTrue( $id1 > 0 );

        $this->dba->execute( $values );
        $id2 = $this->dba->lastId();
        $this->assertNotEquals( $id2, $id1 );
        $this->assertEquals( $id2, $id1 + 1 );
    }
    public function test_insert_with_last_id()
    {
        $insert = "
            INSERT {$this->table}
                ( name, age, bdate, no_null )
            VALUES
                ( 'test dba', 40, '1990-01-02', 'not null' );
        ";
        $this->dba->query( $insert );
        $id1 = $this->dba->lastId();
        $this->assertTrue( $id1 > 0 );

        $this->dba->query( $insert );
        $id2 = $this->dba->lastId();
        $this->assertNotEquals( $id2, $id1 );
        $this->assertEquals( $id2, $id1 + 1 );
    }
    // +----------------------------------------------------------------------+
}
