<?php
namespace wsTests\DbAccess;

require_once( __DIR__ . '/../../autoloader.php' );

class Query_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \wsCore\DbAccess\Query */
    var $query;
    /** @var \wsCore\DbAccess\PdObject */
    var $pdo;
    function setUp()
    {
        /** @var \wsCore\DbAccess\PdObject */
        $this->pdo = new QueryPdoMock();
        /** @var \wsCore\DbAccess\Query */
        $this->query = new \wsCore\DbAccess\Query( $this->pdo );
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
    public function test_values_null_and_empty_string()
    {
        // check setting table name
        $table = 'testTable';
        $values = array( 'col1' => 'val1', 'colNull' => NULL, 'colZero' => '' );
        $this->query->table( $table )->values( $values )->makeUpdate()->exec();

        // check SQL statement
        $this->assertContains( "UPDATE {$table} SET ", $this->pdo->sql );
        foreach( $this->pdo->prep as $key => $val ) {
            $this->assertContains( $key, $this->pdo->sql );
            $this->assertContains( $val, $values );
        }
        // check mock PDO
        $this->assertContains( 'colNull=NULL', $this->pdo->sql );
        $this->assertContains( 'col1=:db_prep_', $this->pdo->sql );
        $this->assertContains( 'colZero=:db_prep_', $this->pdo->sql );

        $this->checkUpdateContainsVal( $this->pdo->sql, 'col1', $values, $this->pdo->prep );
        $this->checkUpdateContainsVal( $this->pdo->sql, 'colZero', $values, $this->pdo->prep );
    }
    public function test_simple_update_statement()
    {
        // check setting table name
        $table = 'testTable';
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->query->table( $table )->values( $values )->makeUpdate()->exec();

        // check SQL statement
        $this->assertContains( "UPDATE {$table} SET ", $this->pdo->sql );
        foreach( $this->pdo->prep as $key => $val ) {
            $this->assertContains( $key, $this->pdo->sql );
            $this->assertContains( $val, $values );
        }
        $this->assertContains( "col1=:db_prep_1", $this->pdo->sql );
        $this->assertContains( "col2=:db_prep_2", $this->pdo->sql );
    }
    public function test_simple_insert_statement()
    {
        $table = 'testTable';
        $this->query->table( $table );
        // check INSERT
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->query->values( $values );
        $this->query->makeInsert();
        $this->query->exec();

        // check SQL statement
        $this->assertContains( "INSERT INTO {$table} ( col1, col2 ) VALUES (", $this->pdo->sql );
        foreach( $this->pdo->prep as $key => $val ) {
            $this->assertContains( $key, $this->pdo->sql );
            $this->assertContains( $val, $values );
        }
    }
    // +----------------------------------------------------------------------+
    public function test_make_simple_select_statement()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table );
        $this->query->makeSelect()->exec();
        $this->assertContains( $table, $this->pdo->sql );
        $this->assertEquals( "SELECT * FROM {$table}", $this->pdo->sql );
    }
    // +----------------------------------------------------------------------+
}