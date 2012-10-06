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
    function test_popHtml()
    {
        $name = $this->friend->popHtml( 'html', 'friend_name', 'my good friend' );
        $this->assertEquals( 'my good friend', $name );
        
        $form = (string) $this->friend->popHtml( 'form', 'friend_name', 'my good friend' );
        $this->assertContains( '<input type="text" name="friend_name" ', $form );
        $this->assertContains( 'value="my good friend"', $form );
    }
    function test_getSelInstance()
    {
        $sel1 = $this->friend->getSelInstance( 'friend_name' );
        $sel2 = $this->friend->getSelInstance( 'friend_name' );
        $this->assertSame( $sel1, $sel2 );
    }
    function test_getSelector()
    {
        $sel = $this->friend->getSelector( 'friend_name' );
        $this->assertTrue( is_object( $sel ) );
        $this->assertEquals( 'wsCore\Html\Selector_Text', get_class( $sel ) );
        $this->assertEquals( '43', $sel->attributes[ 'width'] );

        $sel = $this->friend->getSelector( 'friend_bday' );
        $this->assertTrue( is_object( $sel ) );
        $this->assertEquals( 'wsCore\Html\Selector_DateYMD', get_class( $sel ) );
    }
    function test_dao_basics()
    {
        $id_name = $this->friend->getIdName();
        $this->assertEquals( 'friend_id', $id_name );

        $model_name = $this->friend->getModelName();
        $this->assertEquals( 'wsTests\DbAccess\Dao_Friend', $model_name );

        $prop_name = $this->friend->propertyName( 'friend_name');
        $this->assertEquals( 'name', $prop_name );
        $prop_name = $this->friend->propertyName( 'friend_bday');
        $this->assertEquals( 'birthday', $prop_name );
        
        $data = $this->friend->getRecord();
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->friend->recordClassName(), get_class( $data ) );
    }
    function test_id_is_restricted()
    {
        $values = array(
            'friend_id'   => 101,
            'friend_name' => 'my friend',
            'friend_bday' => '1980-01-23',
        );
        $id = $this->friend->insert( $values );
        $data = $this->friend->find( $id );
        $this->assertEquals( $values[ 'friend_name' ], $data[ 'friend_name' ] );
        $this->assertEquals( $values[ 'friend_bday' ], $data[ 'friend_bday' ] );
        $this->assertNotEquals( $values[ 'friend_id' ], $id );

        $update = array(
            'friend_id'   => 101,
            'friend_name' => 'my real friend',
        );
        $this->friend->update( $id, $update );
        $data = $this->friend->find( $id );
        $this->assertEquals( $update[ 'friend_name' ], $data[ 'friend_name' ] );
        $this->assertEquals( $values[ 'friend_bday' ], $data[ 'friend_bday' ] );
    }
    function test_simple_insert_and_update()
    {
        $values = array(
            'friend_name' => 'my friend',
            'friend_bday' => '1980-01-23',
        );
        $id = $this->friend->insert( $values );
        $update = array(
            'friend_name' => 'my real friend',
        );
        $this->friend->update( $id, $update );
        $data = $this->friend->find( $id );

        $this->assertEquals( $update[ 'friend_name' ], $data[ 'friend_name' ] );
        $this->assertEquals( $values[ 'friend_bday' ], $data[ 'friend_bday' ] );
    }
    function test_simple_insert_and_find() 
    {
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