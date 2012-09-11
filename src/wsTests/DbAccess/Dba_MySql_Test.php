<?php
namespace wsTests\DbAccess;

require_once( __DIR__ . '/../../autoloader.php' );

class Dba_Dba_MySql_Test extends \PHPUnit_Framework_TestCase
{
    var $config = array();
    /** @var \wsCore\DbAccess\Dba */
    var $dba = NULL;
    var $table = 'test_wsCore';
    var $column_list = '';
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->config = array(
            'dsn' => 'db=mysql dbname=test_wsCore username=admin password=admin'
        );
        \wsCore\DbAccess\Rdb::_init();
        \wsCore\DbAccess\Rdb::set( 'config', $this->config );
        $this->dba = new \wsCore\DbAccess\Dba();
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

    public function fill_columns( $max=10 )
    {
        $prepare = "
            INSERT {$this->table}
                ( name, age, bdate, no_null )
            VALUES
                ( :name, :age, :bdate, :no_null );
        ";
        $this->dba->prepare( $prepare );
        for( $i = 0; $i < $max; $i ++ ) {
            $values = $this->get_column_by_row( $i );
            $this->dba->execute( $values );
        }
    }

    public function get_column_by_row( $row )
    {
        $date = new \DateTime( '1980-05-01' );
        $date = $date->add( new \DateInterval( "P{$row}D" ) );
        $values = array(
            ':name' => 'filed #' . $row,
            ':age' => 40 + $row,
            ':bdate' => $date->format( 'Y-m-d' ),
            ':no_null' => 'never null'.($row+1),
        );
        return $values;
    }
    // +----------------------------------------------------------------------+
    public function test_dbConnect_and_new()
    {
        // the original pdo object.
        $pdo = $this->dba->pdo();
        // reconnect with the same config. should reuse the $pdo.
        $this->dba->dbConnect( NULL );
        $pdo1 = $this->dba->pdo();
        // now reconnect with new pdo.
        $this->dba->dbConnect( NULL, TRUE );
        $pdo2 = $this->dba->pdo();

        $this->assertEquals( $pdo, $pdo1 );
        $this->assertSame( $pdo, $pdo1 );

        $this->assertEquals( $pdo1, $pdo2 );
        $this->assertNotSame( $pdo1, $pdo2 );
    }
    public function test_inject_dbConnect()
    {
        // clude test.
        $this->dba->dbConnect( $this );
        $injectedPdo = $this->dba->pdo();
        $this->assertEquals( $this, $injectedPdo );
        $this->assertSame( $this, $injectedPdo );
    }
    public function test_inject_sql()
    {
        $sql = 'injected sql';
        $this->dba->injectSql( $sql );
        $injectedSql = $this->dba->sql();
        $this->assertEquals( $sql, $injectedSql );
    }
    public function test_driver_name()
    {
        $driver = $this->dba->getDriverName();
        $this->assertEquals( 'mysql', $driver );
    }
    public function test_fetchRow()
    {
        $max = 12;
        $this->setUp_TestTable_perm();
        $this->fill_columns( $max );

        // get all data
        $this->dba->execSQL( "SELECT * FROM {$this->table};" );

        // check fetchNumRow
        $numRows = $this->dba->fetchNumRow();
        $this->assertEquals( $max, $numRows );

        $columns = array( 'name', 'age', 'bdate', 'no_null' );
        for( $row = 0; $row < $max; $row ++ ) {
            $rowData = $this->get_column_by_row($row);
            $fetched = $this->dba->fetchRow();
            foreach( $columns as $colName ) {
                $this->assertEquals( $fetched[$colName], $rowData[':'.$colName] );
            }
        }
    }
    public function test_fetchAll()
    {
        $max = 12;
        $this->setUp_TestTable_perm();
        $this->fill_columns( $max );

        // get all data
        $this->dba->execSQL( "SELECT * FROM {$this->table};" );

        // check fetchNumRow
        $numRows = $this->dba->fetchNumRow();
        $this->assertEquals( $max, $numRows );

        $columns = array( 'name', 'age', 'bdate', 'no_null' );
        $allData = $this->dba->fetchAll();
        for( $row = 0; $row < $max; $row ++ ) {
            $rowData = $this->get_column_by_row($row);
            foreach( $columns as $colName ) {
                $this->assertEquals( $allData[$row][$colName], $rowData[':'.$colName] );
            }
        }
    }
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