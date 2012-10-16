<?php
namespace wsTests\DbAccess;

use \wsCore\Core;
require_once( __DIR__ . '/../../autoloader.php' );

class Relation_MySql_Test extends \PHPUnit_Framework_TestCase
{
    /** @var mixed */
    public $config;

    /** @var \wsCore\DbAccess\Query */
    public $query;

    /** @var Dao_Friend */
    public $friend;

    /** @var Dao_Contact */
    public $contact;
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
    // +----------------------------------------------------------------------+
    function test_simple_HasOne()
    {
        // create a friend data.
        $dataFriend = Dao_SetUp::makeFriend();
        $id1 = $this->friend->insert( $dataFriend );
        $friend = $this->friend->find( $id1 );

        // create a contact with a relation to the friend data.
        $dataContact = Dao_SetUp::makeContact();
        $contact = $this->contact->getRecord();
        $contact->load( $dataContact );
        $contact->relation( 'friend_id' )->set( $friend );
        $contact->insert();
        $this->assertEquals( $id1, $contact->get( 'friend_id' ) );

        // read the contact, and get the friend data via relation
        $id2 = $contact->getId();
        $contact2 = $this->contact->find( $id2 );
        $friend2 = $contact2->relation( 'friend_id' )->get();
        $this->assertTrue( is_array( $friend2 ) );
        $friend2 = $friend2[0];
        $this->assertEquals( $id1, $friend2->getId() );
    }
    function test_simple_HasRefs()
    {
        // create a friend data.
        $dataFriend = Dao_SetUp::makeFriend();
        $id1 = $this->friend->insert( $dataFriend );
        $friend = $this->friend->find( $id1 );

        // create a contact with a relation with the friend.
        $dataContact = Dao_SetUp::makeContact();
        $contact1 = $this->contact->getRecord();
        $contact1->load( $dataContact );
        $friend->relation( 'contact' )->set( $contact1 );
        $contact1->insert();

        $this->assertEquals( $id1, $contact1->get( 'friend_id' ) );

        // create another contact with the friendship.
        $contact2 = $this->contact->getRecord();
        $contact2->load( Dao_SetUp::makeContact(2) );
        $friend->relation( 'contact' )->set( $contact2 );
        $contact2->insert();

        $this->assertEquals( $id1, $contact2->get( 'friend_id' ) );

        // get contacts from friend.
        $contacts = $friend->relation( 'contact' )->get();
        $con1 = $contacts[0];
        $con2 = $contacts[1];
        $this->assertEquals( $contact1->getId(), $con1->getId() );
        $this->assertEquals( $contact2->getId(), $con2->getId() );
        $this->assertEquals( $contact1->get( 'contact_name' ), $con1->get( 'contact_name' ) );
        $this->assertEquals( $contact2->get( 'contact_name' ), $con2->get( 'contact_name' ) );
    }
}

    