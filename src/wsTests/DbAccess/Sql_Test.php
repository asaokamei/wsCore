<?php
namespace wsTests\DbAccess;

require_once( __DIR__ . '/../../autoloader.php' );

class Dba_Sql_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \wsCore\DbAccess\Sql */
    var $sql;
    /** @var \wsCore\DbAccess\Rdb */
    var $pdo;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->pdo = new SqlMockDba();
        $this->sql = new \wsCore\DbAccess\Sql( $this->pdo );
    }
    public function getValFromUpdate( $sql, $name ) {
        preg_match( "/{$name}=(:db_prep_[0-9]+)/", $sql, $matches );
        return $matches[1];
    }
    public function checkUpdateContainsVal( $sql, $name, $values, $prepared ) {
        $prep1 = $this->getValFromUpdate( $sql, $name );
        $val1  = $prepared[ $prep1 ];
        $this->assertEquals( $values[ $name ], $val1 );
    }
    // +----------------------------------------------------------------------+
    public function test_1()
    {
    }
    // +----------------------------------------------------------------------+
    public function test_values_null_and_empty_string()
    {
        // check setting table name
        $table = 'testTable';
        $this->sql->table( $table );
        $this->assertEquals( $table, $this->sql->table );

        // check UPDATE
        $values = array( 'col1' => 'val1', 'colNull' => NULL, 'colZero' => '' );
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
        $this->assertContains( 'colNull=NULL', $this->pdo->sql );
        $this->assertContains( 'col1=:db_prep_', $this->pdo->sql );
        $this->assertContains( 'colZero=:db_prep_', $this->pdo->sql );

        $this->checkUpdateContainsVal( $this->pdo->sql, 'col1', $values, $this->pdo->prep );
        $this->checkUpdateContainsVal( $this->pdo->sql, 'colZero', $values, $this->pdo->prep );

        echo $this->pdo->sql;
        var_dump( $this->pdo->prep );
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
        $this->checkUpdateContainsVal( $this->pdo->sql, 'col1', $values, $this->pdo->prep );
        $this->checkUpdateContainsVal( $this->pdo->sql, 'col2', $values, $this->pdo->prep );
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

