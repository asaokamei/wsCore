<?php
namespace wsTests\DbAccess;

use \wsCore\Core;
require_once( __DIR__ . '/../../autoloader.php' );

class DataRecord_MySql_Test extends \PHPUnit_Framework_TestCase
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
    /**
     *
     */
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

        $record = $this->friend->getRecord();
        $values = array(
            'friend_name' => 'my friend2',
            'friend_bday' => '1990-03-21',
        );
        $record->load( $values );
        $record->insert();
        $id2 = $record->getId();

        $this->assertNotEquals( $id, $id2 );
        
        $data = $this->friend->find( $id2 );
        $this->assertEquals( $values[ 'friend_name' ], $data->get( 'friend_name' ) );
        $this->assertEquals( $values[ 'friend_bday' ], $data[ 'friend_bday' ] );
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->friend->recordClassName(), get_class( $data ) );
    }

    /**
     * 
     */
    public function test_simple_insert_and_update()
    {
        $record = $this->friend->getRecord();
        $values = array(
            'friend_name' => 'my friend',
            'friend_bday' => '1980-01-23',
        );
        $record->load( $values );
        $record->insert();
        
        $name = 'my old friend';
        $bday = '1990-12-31';
        $record->set( 'friend_name', $name );
        $record[ 'friend_bday' ] = $bday;
        $record->update();
        
        $id = $record->getId();
        $data = $this->friend->find( $id );
        $this->assertEquals( $name, $data[ 'friend_name' ] );
        $this->assertEquals( $bday, $data[ 'friend_bday' ] );
    }

    /**
     * 
     */
    public function test_simple_delete()
    {
        $values = array(
            'friend_name' => 'my friend',
            'friend_bday' => '1980-01-23',
        );
        $id = $this->friend->insert( $values );
        $record = $this->friend->find( $id );
        $record->delete();
        
        $data = $this->query->table( 'friend' )->w( 'friend_id' )->eq( $id )->select();
        $this->assertEmpty( $data );
    }

    /**
     * 
     */
    public function test_basic()
    {
        $record = $this->friend->getRecord();
        $values = array(
            'friend_name' => 'my friend',
            'friend_bday' => '1980-01-23',
        );
        $record->load( $values );
        $record->insert();
        
        $this->assertEquals( 'wsTests\DbAccess\Dao_Friend', $record->getModel() );
        $this->assertFalse( isset( $record[ 'not exists' ] ) );
    }

    /**
     * 
     */
    public function test_validator()
    {
        $record = $this->friend->getRecord();
        $values = array(
            'friend_name' => 'my friend',
            'friend_bday' => '1980-01-23',
        );
        $record->load( $values );
        
        $dio = Core::get( 'DataIO' );
        $record->validate( $dio );
        $this->assertTrue( $record->isValid() );
        
        $record->set( 'friend_bday', '1234567890' ); // faulty date.
        $record->validate( $dio );
        $this->assertFalse( $record->isValid() );
    }

    /**
     * 
     */
    function test_popHtml()
    {
        // set data
        $record = $this->friend->getRecord();
        $values = array(
            'friend_name' => 'he\'s friend',
            'friend_bday' => '1980-01-23',
        );
        $record->load( $values );

        // test getting html, a web-safe value.
        $record->setHtmlType( 'html' );
        $html = (string) $record->popHtml( 'friend_name' );
        $this->assertContains( htmlentities( 'he\'s friend', ENT_QUOTES, 'UTF-8' ), $html );

        $html = (string) $record->popHtml( 'friend_bday' );
        $this->assertContains( '1980/01/23', $html );
        
        $record->setHtmlType( 'form' );
        $html = (string) $record->popHtml( 'friend_name' );
        $this->assertContains( '<input type="text" name="friend_name" ', $html );
        $this->assertContains( ' value="' . htmlentities( 'he\'s friend', ENT_QUOTES, 'UTF-8' ) . '" ', $html );

        $html = (string) $record->popHtml( 'friend_bday' );
        $this->assertContains( '<select name="friend_bday_y" ', $html );
        $this->assertContains( '<select name="friend_bday_m" ', $html );
        $this->assertContains( '<select name="friend_bday_d" ', $html );
    }
    // +----------------------------------------------------------------------+    
}