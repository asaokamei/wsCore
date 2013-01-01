<?php
namespace wsTests\DbAccess;
use \WScore\Core;

require_once( __DIR__ . '/../../autoloader.php' );

/*
 * TODO: more test on Query. and check the overall design as well.
 */

class Query_MySql_Test extends \PHPUnit_Framework_TestCase
{
    var $config = array();
    /** @var \WScore\DbAccess\Query */
    var $query = NULL;
    var $table = 'test_WScore';
    var $column_list = '';
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->config = 'dsn=mysql:dbname=test_WScore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        /** @var \WScore\DbAccess\Query */
        $this->query = Core::get( 'Query');
        $this->column_list = '
            id int NOT NULL AUTO_INCREMENT,
            name VARCHAR(30),
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
        $this->table = 'test_WScorePerm';
        $this->setUp_TestTable();
    }

    /**
     * creates new table for testing.
     */
    public function setUp_TestTable()
    {
        $this->query->execSQL( "DROP TABLE IF EXISTS {$this->table};" );
        $this->query->execSQL( "
        CREATE TABLE {$this->table} ( {$this->column_list} );
        " );
    }

    public function fill_columns( $max=10 )
    {
        $prepare = "
            INSERT INTO {$this->table}
                ( name, age, bdate, no_null )
            VALUES
                ( :name, :age, :bdate, :no_null );
        ";
        $this->query->execPrepare( $prepare );
        for( $i = 0; $i < $max; $i ++ ) {
            $values = $this->get_column_by_row( $i );
            $this->query->execExecute( $values );
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
    public function get_value_by_row( $row )
    {
        $column = $this->get_column_by_row( $row );
        $values = array();
        foreach( $column as $key => $val ) {
            $values[ substr( $key, 1 ) ] = $val;
        }
        return $values;
    }
    // +----------------------------------------------------------------------+
    public function test_insert_data()
    {
        $data = $this->get_value_by_row( 21 );

        // add some data
        $return = $this->query->table( $this->table )->insert( $data );
        $this->assertEquals( 'WScore\DbAccess\Query', get_class( $return ) );
        // last ID should be 1, since it is the first data.
        $id = $this->query->lastId();
        $this->assertEquals( '1', $id );

        // now check to see really added
        $return2 = $this->query->table( $this->table )
            ->where( 'id', $id )->select();
        $this->assertTrue( is_array( $return2 ) );
    }
    public function test_driver_name()
    {
        $driver = $this->query->getDriverName();
        $this->assertEquals( 'mysql', $driver );
    }
    public function test_fetchRow()
    {
        $this->setUp_TestTable_perm();
        $max = 12;
        $this->fill_columns( $max );

        // get all data
        $this->query->execSQL( "SELECT * FROM {$this->table};" );

        // check fetchNumRow
        $numRows = $this->query->fetchNumRow();
        $this->assertEquals( $max, $numRows );

        $columns = array( 'name', 'age', 'bdate', 'no_null' );
        for( $row = 0; $row < $max; $row ++ ) {
            $rowData = $this->get_column_by_row($row);
            $fetched = $this->query->fetchRow();
            foreach( $columns as $colName ) {
                $this->assertEquals( $fetched[$colName], $rowData[':'.$colName] );
            }
        }
    }
    public function test_fetchAll()
    {
        $max = 12;
        $this->fill_columns( $max );

        // get all data
        $this->query->execSQL( "SELECT * FROM {$this->table};" );

        // check fetchNumRow
        $numRows = $this->query->fetchNumRow();
        $this->assertEquals( $max, $numRows );

        $columns = array( 'name', 'age', 'bdate', 'no_null' );
        $allData = $this->query->fetchAll();
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
            INSERT INTO {$this->table}
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
        $this->query->execPrepare( $prepare );
        $this->query->execExecute( $values );
        $id1 = $this->query->lastId();
        $this->assertTrue( $id1 > 0 );

        $this->query->execExecute( $values );
        $id2 = $this->query->lastId();
        $this->assertNotEquals( $id2, $id1 );
        $this->assertEquals( $id2, $id1 + 1 );
    }
    public function test_insert_with_last_id()
    {
        $insert = "
            INSERT INTO {$this->table}
                ( name, age, bdate, no_null )
            VALUES
                ( 'test query', 40, '1990-01-02', 'not null' );
        ";
        $this->query->execSQL( $insert );
        $id1 = $this->query->lastId();
        $this->assertTrue( $id1 > 0 );

        $this->query->execSQL( $insert );
        $id2 = $this->query->lastId();
        $this->assertNotEquals( $id2, $id1 );
        $this->assertEquals( $id2, $id1 + 1 );
    }
    // +----------------------------------------------------------------------+
}
