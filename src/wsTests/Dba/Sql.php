<?php
namespace wsTests\Dba;

require_once( __DIR__ . '/../../autoloader.php' );

class Dba_Sql_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \wsCore\Dba\Sql */
    var $sql;
    /** @var \wsCore\Dba\Pdo */
    var $pdo;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->pdo = new SqlMockDba();
        $this->sql = new \wsCore\Dba\Sql( $this->pdo );
    }
    // +----------------------------------------------------------------------+
    public function test_1()
    {
    }
    // +----------------------------------------------------------------------+
    public function test_update()
    {
        // check setting table name
        $table = 'testTable';
        $this->sql->table( $table );
        $this->assertEquals( $table, $this->sql->table );

        // check UPDATE
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->sql->values( $values );
        $this->sql->makeSQL( 'UPDATE' );
        $this->sql->exec();

        // check SQL statement
        $this->assertContains( "UPDATE {$table} SET ", $this->sql->sql );
        foreach( $this->sql->prepared_values as $key => $val ) {
            $this->assertContains( $key, $this->sql->sql );
            $this->assertContains( $val, $values );
        }
        // check mock PDO
        $this->assertEquals( $this->sql->sql, $this->pdo->sql );
        foreach( $this->pdo->prep as $key => $val ) {
            $this->assertContains( $key, $this->pdo->sql );
            $this->assertContains( $val, $values );
        }
    }
    // +----------------------------------------------------------------------+
    public function test_insert()
    {
        $table = 'testTable';
        $this->sql->table( $table );
        // check INSERT
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->sql->values( $values );
        $this->sql->makeSQL( 'INSERT' );
        $this->sql->exec();

        // check SQL statement
        $this->assertContains( "INSERT INTO {$table} ( col1, col2 ) VALUES (", $this->sql->sql );
        foreach( $this->sql->prepared_values as $key => $val ) {
            $this->assertContains( $key, $this->sql->sql );
            $this->assertContains( $val, $values );
        }
        // check mock PDO
        $this->assertEquals( $this->sql->sql, $this->pdo->sql );
        foreach( $this->pdo->prep as $key => $val ) {
            $this->assertContains( $key, $this->pdo->sql );
            $this->assertContains( $val, $values );
        }
    }
    // +----------------------------------------------------------------------+
    public function test_setting_table()
    {
        // check setting table name
        $table = 'testTable';
        $this->sql->table( $table );
        $this->assertEquals( $table, $this->sql->table );
    }
    // +----------------------------------------------------------------------+
}

