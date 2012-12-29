<?php
namespace wsTests\DbAccess;

use \WScore\Core;
require_once( __DIR__ . '/../../autoloader.php' );

class Relation_MySql_Test extends \PHPUnit_Framework_TestCase
{
    /** @var mixed */
    public $config;

    /** @var \WScore\DbAccess\Query */
    public $query;

    /** @var \WScore\DataMapper\EntityManager */
    public $em;
    
    /** @var Dao_Friend */
    public $friend;

    /** @var Dao_Contact */
    public $contact;
    // +----------------------------------------------------------------------+
    function setUp()
    {
        $this->config = 'db=mysql dbname=test_WScore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        $this->query = Core::get( 'Query' );

        $this->setupFriend();
        $this->setupContact();

        $this->em      = Core::get( 'EntityManager' );
        $this->friend  = Core::get( '\wsTests\DbAccess\Dao_Friend' );
        $this->contact = Core::get( '\wsTests\DbAccess\Dao_Contact' );
        class_exists( '\WScore\DbAccess\Relation' );
        class_exists( '\WScore\DbAccess\Relation_HasRefs' );
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
        $contact = $this->contact->getRecord( $dataContact );
        $this->contact->relation( $contact, 'friend' )->set( $friend );
        $this->contact->insert( $contact );
        $this->assertEquals( $id1, $contact[ 'friend_id' ] );

        // read the contact, and get the friend data via relation
        $id2 = $contact[ 'friend_id' ];
        $contact2 = $this->contact->find( $id2 );
        $friend2 = $this->contact->relation( $contact2, 'friend' )->get();
        $this->assertTrue( is_array( $friend2 ) );
        $friend2 = $friend2[0];
        $this->assertEquals( $id1, $friend2[ 'friend_id' ] );
    }
    function test_simple_HasRefs()
    {
        // create a friend data.
        $dataFriend = Dao_SetUp::makeFriend();
        $id1 = $this->friend->insert( $dataFriend );
        $friend = $this->friend->find( $id1 );

        // create a contact with a relation with the friend.
        $dataContact = Dao_SetUp::makeContact();
        $contact1 = $this->contact->getRecord( $dataContact );
        $relation = $this->friend->relation( $friend, 'contact' );
        $relation->set( $contact1 );
        $this->contact->insert( $contact1 );

        $this->assertEquals( $id1, $contact1[ 'friend_id' ] );

        // create another contact with the friendship.
        $contact2 = $this->contact->getRecord( Dao_SetUp::makeContact(2) );
        $relation->set( $contact2 );
        $this->contact->insert( $contact2 );

        $this->assertEquals( $id1, $contact2[ 'friend_id' ] );

        // get contacts from friend.
        $contacts = $relation->get();
        $con1 = array_shift( $contacts );
        $con2 = array_shift( $contacts );
        $this->assertEquals( $contact1[ 'contact_id' ], $con1->contact_id );
        $this->assertEquals( $contact2[ 'contact_id' ], $con2->contact_id );
        $this->assertEquals( $contact1[ 'contact_info' ], $con1->contact_info );
        $this->assertEquals( $contact2[ 'contact_info' ], $con2->contact_info );
    }
    function test_HasOne_del()
    {
        // make friend and contact
        $idFriend  = $this->friend->insert( Dao_SetUp::makeFriend() );
        $friend    = $this->em->getEntity( 'wsTests\DbAccess\Dao_Friend', $idFriend );
        $contact   = $this->em->newEntity( 'wsTests\DbAccess\Dao_Contact', Dao_SetUp::makeContact() );
        $this->contact->relation( $contact, 'friend' )->set( $friend );
        $this->em->save();

        // delete relation. 
        $newContact = $this->em->getEntity( 'wsTests\DbAccess\Dao_Contact', $contact[ 'contact_id' ] );
        $this->contact->relation( $newContact, 'friend' )->del();
        $this->em->save();

        // verify relation is deleted.
        $finalContact = $this->contact->find( $contact->_get_Identifier() );
        $this->assertEquals( null, $finalContact[ 'friend_id' ] );
    }
    function test_HasRefs_del()
    {
        // make friend and contact
        $idFriend  = $this->friend->insert( Dao_SetUp::makeFriend() );
        $friend    = $this->em->getEntity( 'wsTests\DbAccess\Dao_Friend', $idFriend );
        $contact   = $this->em->newEntity( 'wsTests\DbAccess\Dao_Contact', Dao_SetUp::makeContact() );
        $this->em->relation( $contact, 'friend' )->set( $friend );
        $this->em->save();
        
        // delete relation. 
        $newFriend = $this->em->getEntity( 'wsTests\DbAccess\Dao_Friend', $idFriend );
        $this->em->relation( $newFriend, 'contact' )->del();
        $this->em->save();

        // verify relation is deleted.
        $finalContact = $this->contact->find( $contact->_get_Identifier() );
        $this->assertEquals( null, $finalContact[ 'friend_id' ] );
    }
}

    