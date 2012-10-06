<?php
namespace wsTests\DbAccess;

use \wsCore\Core;
require_once( __DIR__ . '/../../autoloader.php' );

class Dao_MySql_Test extends \PHPUnit_Framework_TestCase
{
    /** @var mixed */
    public $config;

    /** @var \wsCore\DbAccess\Query */
    public $query;
    
    /** @var Dao_Friend */
    public $friend;
    // +----------------------------------------------------------------------+
    function setUp()
    {
        $this->config = 'db=mysql dbname=test_wsCore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        $this->query = Core::get( 'Query' );
        
        $this->setupFriend();
        
        $this->friend = Core::get( '\wsTests\DbAccess\Dao_Friend' );
    }

    /**
     * @param string $table
     */
    function setupFriend( $table='friend' ) 
    {
        $sql = "DROP TABLE IF EXISTS {$table}";
        $this->query->execSQL( $sql );
        $sql = "
            CREATE TABLE {$table} (
              friend_id    SERIAL, 
              friend_name  text, 
              friend_bday  date,
              created_at   datetime,
              updated_at   datetime,
              constraint friend_pkey PRIMARY KEY (
                friend_id
              )
            )
        ";
        $this->query->execSQL( $sql );
    }
    // +----------------------------------------------------------------------+
    function test_simple_insert_and_find() {
        $values = array(
            'friend_name' => 'my friend',
            'friend_bday' => '1980-01-23',
        );
        $id = $this->friend->insert( $values );
        $data = $this->friend->find( $id );

        $this->assertEquals( $values[ 'friend_name' ], $data[ 'friend_name' ] );
        $this->assertEquals( $values[ 'friend_bday' ], $data[ 'friend_bday' ] );
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->friend->recordClassName(), get_class( $data ) );

        $values = array(
            'friend_name' => 'my friend2',
            'friend_bday' => '1990-03-21',
        );
        $id = $this->friend->insert( $values );
        $data = $this->friend->find( $id );

        $this->assertEquals( $values[ 'friend_name' ], $data[ 'friend_name' ] );
        $this->assertEquals( $values[ 'friend_bday' ], $data[ 'friend_bday' ] );
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->friend->recordClassName(), get_class( $data ) );
    }
    // +----------------------------------------------------------------------+
}