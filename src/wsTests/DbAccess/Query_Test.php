<?php
namespace wsTests\DbAccess;

require_once( __DIR__ . '/../../autoloader.php' );

class Query_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \WScore\DbAccess\Query */
    var $query;
    /** @var Mock_QueryPdo|\WScore\DbAccess\PdObject */
    var $pdo;
    function setUp()
    {
        /** @var Mock_QueryPdo */
        $this->pdo = new Mock_QueryPdo();
        /** @var \WScore\DbAccess\Query */
        $this->query = new \WScore\DbAccess\Query( $this->pdo );
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
    public function test_select_with_many_option()
    {
        $table = 'testTable';
        $this->query->table( $table )->select();
        $this->assertEquals( "SELECT * FROM {$table}", $this->pdo->sql );
    }
    public function test_select_with_order()
    {
        $table = 'testTable';
        $this->query->table( $table )->order( 'test order' )->select();
        $this->assertEquals( "SELECT * FROM {$table} ORDER BY test order", $this->pdo->sql );
    }
    public function test_select_with_group()
    {
        $table = 'testTable';
        $this->query->table( $table )->group( 'test group' )->select();
        $this->assertEquals( "SELECT * FROM {$table} GROUP BY test group", $this->pdo->sql );
    }
    public function test_select_with_misc()
    {
        $table = 'testTable';
        $this->query->table( $table )->misc( 'test misc' )->select();
        $this->assertEquals( "SELECT * FROM {$table} test misc", $this->pdo->sql );
    }
    public function test_select_with_limit()
    {
        $table = 'testTable';
        $this->query->table( $table )->limit(10)->select();
        $this->assertEquals( "SELECT * FROM {$table} LIMIT 10", $this->pdo->sql );
    }
    public function test_select_with_offset()
    {
        $table = 'testTable';
        $this->query->table( $table )->offset(5)->select();
        $this->assertEquals( "SELECT * FROM {$table} OFFSET 5", $this->pdo->sql );
    }
    // +----------------------------------------------------------------------+
    function test_where_with_get() 
    {
        $table = 'testTable';
        $this->query->table( $table )->abc->like( '%val%' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE abc LIKE :db_prep_1", $this->pdo->sql );
        // add one more where clause
        $this->query->xyz->like( '%string%' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE abc LIKE :db_prep_1 "
            . "AND xyz LIKE :db_prep_2", $this->pdo->sql );
    }
    public function test_where_w_and_like()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->like( '%val%' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a LIKE :db_prep_1", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->like( '%string%' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a LIKE :db_prep_1 "
            . "AND x LIKE :db_prep_2", $this->pdo->sql );
    }
    public function test_where_w_and_notNull()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->notNull( array('b','c') )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a IS NOT NULL", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->notNull( array( 'y', 'z' ) )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a IS NOT NULL "
            . "AND x IS NOT NULL", $this->pdo->sql );
    }
    public function test_where_w_and_isNull()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->isNull( array('b','c') )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a IS NULL", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->isNull( array( 'y', 'z' ) )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a IS NULL "
            . "AND x IS NULL", $this->pdo->sql );
    }
    public function test_where_w_and_between()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->between( array('b','c') )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a BETWEEN :db_prep_1 AND :db_prep_2", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->between( array( 'y', 'z' ) )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a BETWEEN :db_prep_1 AND :db_prep_2 "
            . "AND x BETWEEN :db_prep_3 AND :db_prep_4", $this->pdo->sql );
    }
    public function test_where_w_and_notIn()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->notIn( array('b') )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a NOT IN ( :db_prep_1 )", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->notIn( array( 'y', 'z' ) )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a NOT IN ( :db_prep_1 ) "
            . "AND x NOT IN ( :db_prep_2, :db_prep_3 )", $this->pdo->sql );
    }
    public function test_where_w_and_in()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->in( array('b') )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a IN ( :db_prep_1 )", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->in( array( 'y', 'z' ) )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a IN ( :db_prep_1 ) " 
            . "AND x IN ( :db_prep_2, :db_prep_3 )", $this->pdo->sql );
    }
    public function test_where_w_and_ge()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->ge( 'b' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a >= :db_prep_1", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->ge( 'z' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a >= :db_prep_1 AND x >= :db_prep_2", $this->pdo->sql );
    }
    public function test_where_w_and_gt()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->gt( 'b' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a > :db_prep_1", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->gt( 'z' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a > :db_prep_1 AND x > :db_prep_2", $this->pdo->sql );
    }
    public function test_where_w_and_le()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->le( 'b' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a <= :db_prep_1", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->le( 'z' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a <= :db_prep_1 AND x <= :db_prep_2", $this->pdo->sql );
    }
    public function test_where_w_and_lt()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->lt( 'b' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a < :db_prep_1", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->lt( 'z' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a < :db_prep_1 AND x < :db_prep_2", $this->pdo->sql );
    }
    public function test_where_w_and_ne()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->ne( 'b' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a != :db_prep_1", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->ne( 'z' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a != :db_prep_1 AND x != :db_prep_2", $this->pdo->sql );
    }
    public function test_where_clause_where()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table )->where( 'a', 'b', 'c' )->makeSelect()->exec();
        $select = "SELECT * FROM {$table} WHERE a C :db_prep_1";
        $this->assertEquals( $select, $this->pdo->sql );
        // add one more where clause
        $this->query->where( 'x', 'y', 'z' )->makeSelect()->exec();
        $select .= " AND x Z :db_prep_2";
        $this->assertEquals( $select, $this->pdo->sql );
        // add one more whereRaw clause. should be as is.
        $this->query->whereRaw( '1', '2', '3' )->makeSelect()->exec();
        $select .= " AND 1 3 2";
        $this->assertEquals( $select, $this->pdo->sql );
    }
    public function test_where_w_and_eq()
    {
        // test setting column in select
        $table = 'testTable';
        $this->query->table( $table )->w( 'a' )->eq( 'b' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a = :db_prep_1", $this->pdo->sql );
        // add one more where clause
        $this->query->w( 'x' )->eq( 'z' )->select();
        $this->assertEquals( "SELECT * FROM {$table} WHERE a = :db_prep_1 AND x = :db_prep_2", $this->pdo->sql );
    }
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
    // +----------------------------------------------------------------------+
    /**
     * @expectedException \RuntimeException
     */
    public function test_simple_delete_no_where()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table )->makeDelete()->exec();
    }
    public function test_simple_delete()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table )->w( 'id' )->eq(10)->makeDelete()->exec();
        $this->assertContains( $table, $this->pdo->sql );
        $this->assertEquals( "DELETE FROM {$table} WHERE id = :db_prep_1", $this->pdo->sql );
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
    public function test_simple_update_statement2()
    {
        // check setting table name
        $table = 'testTable';
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->query->table( $table )->update( $values );

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
        $this->query->values( $values )->makeInsert()->exec();

        // check SQL statement
        $this->assertContains( "INSERT INTO {$table} ( col1, col2 ) VALUES (", $this->pdo->sql );
        foreach( $this->pdo->prep as $key => $val ) {
            $this->assertContains( $key, $this->pdo->sql );
            $this->assertContains( $val, $values );
        }
    }
    public function test_simple_insert_statement2()
    {
        $table = 'testTable';
        $this->query->table( $table );
        // check INSERT
        $values = array( 'col1' => 'val1', 'col2' => 'val2' );
        $this->query->insert( $values );

        // check SQL statement
        $this->assertContains( "INSERT INTO {$table} ( col1, col2 ) VALUES (", $this->pdo->sql );
        foreach( $this->pdo->prep as $key => $val ) {
            $this->assertContains( $key, $this->pdo->sql );
            $this->assertContains( $val, $values );
        }
    }
    // +----------------------------------------------------------------------+
    public function test_make_simple_count_statement()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table );
        $this->query->makeCount()->exec();
        $this->assertContains( $table, $this->pdo->sql );
        $this->assertEquals( "SELECT COUNT(*) AS WScore__Count__ FROM {$table}", $this->pdo->sql );

        // test setting column in select
        $this->query->column( 'colA' )->makeCount()->exec();
        $this->assertEquals( "SELECT COUNT(*) AS WScore__Count__ FROM {$table}", $this->pdo->sql );
    }
    public function test_make_simple_select_statement()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table );
        $this->query->makeSelect()->exec();
        $this->assertContains( $table, $this->pdo->sql );
        $this->assertEquals( "SELECT * FROM {$table}", $this->pdo->sql );

        // test setting column in select
        $this->query->column( 'colA' )->makeSelect()->exec();
        $this->assertEquals( "SELECT colA FROM {$table}", $this->pdo->sql );

        // test quick select method with column
        $this->query->select( 'colX' );
        $this->assertEquals( "SELECT colX FROM {$table}", $this->pdo->sql );
    }
    public function test_select_for_update()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table );
        $this->query->forUpdate()->makeSelect()->exec();
        $this->assertContains( $table, $this->pdo->sql );
        $this->assertEquals( "SELECT * FROM {$table} FOR UPDATE", $this->pdo->sql );

        // test setting column in select
        $this->query->column( 'colA' )->makeSelect()->exec();
        $this->assertEquals( "SELECT colA FROM {$table} FOR UPDATE", $this->pdo->sql );

        // test quick select method with column
        $this->query->select( 'colX' );
        $this->assertEquals( "SELECT colX FROM {$table} FOR UPDATE", $this->pdo->sql );
    }
    public function test_select_distinct()
    {
        // check setting table name
        $table = 'testTable';
        $this->query->table( $table );
        $this->query->distinct()->makeSelect()->exec();
        $this->assertContains( $table, $this->pdo->sql );
        $this->assertEquals( "SELECT DISTINCT * FROM {$table}", $this->pdo->sql );

        // test setting column in select
        $this->query->column( 'colA' )->makeSelect()->exec();
        $this->assertEquals( "SELECT DISTINCT colA FROM {$table}", $this->pdo->sql );

        // test quick select method with column
        $this->query->select( 'colX' );
        $this->assertEquals( "SELECT DISTINCT colX FROM {$table}", $this->pdo->sql );
    }
    // +----------------------------------------------------------------------+
}