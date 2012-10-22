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
    
    /** @var Dao_Contact */
    public $contact;

    /** @var Dao_Group */
    public $group;
    // +----------------------------------------------------------------------+
    function setUp()
    {
        $this->config = 'db=mysql dbname=test_wsCore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        $this->query = Core::get( 'Query' );

        $this->setupFriend();
        $this->setupContact();

        $this->friend  = Core::get( '\wsTests\DbAccess\Dao_Friend' );
        $this->contact = Core::get( '\wsTests\DbAccess\Dao_Contact' );
        $this->group   = Core::get( '\wsTests\DbAccess\Dao_Group' );
    }

    /**
     * @param string $table
     */
    function setupFriend( $table='friend' )
    {
        $this->query->execSQL( Dao_SetUp::clearFriend( $table ) );
        $this->query->execSQL( Dao_SetUp::setupFriend( $table ) );
    }
    /**
     * @param string $table
     */
    function setupContact( $table='contact' )
    {
        $this->query->execSQL( Dao_SetUp::clearContact( $table ) );
        $this->query->execSQL( Dao_SetUp::setupContact( $table ) );
    }
    /**
     * @param string $table
     */
    function setupGroup( $table='myGroup' )
    {
        $this->query->execSQL( Dao_SetUp::clearGroup( $table ) );
        $this->query->execSQL( Dao_SetUp::setupGroup( $table ) );
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
        $record->set( $values );
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
        $record->set( $values );
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
        $record->deleteRecord();
        
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
        $record->set( $values );
        $record->insert();
        
        $this->assertEquals( 'Dao_Friend', $record->getModel() );
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
        $record->set( $values );
        
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
        $record->set( $values );

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

    /**
     *
     */
    function test_created_and_updated_at()
    {
        $values = Dao_SetUp::makeFriend();
        $id = $this->friend->insert( $values );
        $data = $this->friend->find( $id );

        $this->assertEquals( $values[ 'friend_name' ], $data[ 'friend_name' ] );
        $this->assertEquals( $values[ 'friend_bday' ], $data[ 'friend_bday' ] );
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->friend->recordClassName(), get_class( $data ) );

        $record = $this->friend->getRecord();
        $values = Dao_SetUp::makeFriend(1);
        $record->set( $values );
        $record->insert();
        $id2 = $record->getId();

        $this->assertNotEquals( $id, $id2 );

        $data = $this->friend->find( $id2 );
        $this->assertEquals( $values[ 'friend_name' ], $data->get( 'friend_name' ) );
        $this->assertEquals( $values[ 'friend_bday' ], $data[ 'friend_bday' ] );
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->friend->recordClassName(), get_class( $data ) );
    }

    function test_contact_basic_function()
    {
        // add new data. 
        $values = Dao_SetUp::makeContact();
        $id = $this->contact->insert( $values );
        $data = $this->contact->find( $id );

        $this->assertEquals( $values[ 'contact_info' ], $data[ 'contact_info' ] );
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->contact->recordClassName(), get_class( $data ) );

        // add new data by load/insert. 
        $record = $this->contact->getRecord();
        $values = Dao_SetUp::makeContact(1);
        $record->set( $values );
        $record->insert();
        $id2 = $record->getId();

        $this->assertNotEquals( $id, $id2 );

        // update data.
        $data = $this->contact->find( $id2 );
        $this->assertEquals( $values[ 'contact_info' ], $data->get( 'contact_info' ) );
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->contact->recordClassName(), get_class( $data ) );

        $name = 'new contact';
        $record->set( 'contact_info', $name );
        $record->update();

        $id = $record->getId();
        $data = $this->contact->find( $id );
        $this->assertEquals( $name, $data[ 'contact_info' ] );
    }
    function test_group_basic_function()
    {
        $this->setupGroup();
        // add new data.
        $values = Dao_SetUp::makeGroup();
        $id = $this->group->insert( $values );
        $data = $this->group->find( $id );

        $this->assertEquals( $values[ 'group_name' ], $data[ 'group_name' ] );
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->group->recordClassName(), get_class( $data ) );

        // add new data by load/insert.
        $record = $this->group->getRecord();
        $values = Dao_SetUp::makeGroup(1);
        $record->set( $values );
        $record->insert();
        $id2 = $record->getId();

        $this->assertNotEquals( $id, $id2 );

        // update data.
        $data = $this->group->find( $id2 );
        $this->assertEquals( $values[ 'group_name' ], $data->get( 'group_name' ) );
        $this->assertTrue( is_object( $data ) );
        $this->assertEquals( $this->group->recordClassName(), get_class( $data ) );

        $name = 'new group';
        $record->set( 'group_name', $name );
        $record->update();

        $id = $record->getId();
        $data = $this->group->find( $id );
        $this->assertEquals( $name, $data[ 'group_name' ] );
    }
    // +----------------------------------------------------------------------+
}