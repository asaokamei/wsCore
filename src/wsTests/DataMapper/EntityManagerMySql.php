<?php
namespace wsTests\DataMapper;

require_once( __DIR__ . '/../../autoloader.php' );
use \WScore\Core;

class EntityManagerMySql extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    public $config;

    /** @var \WScore\DbAccess\Query */
    public $query;

    /** @var \wsTests\DataMapper\Mock\EntityManager */
    public $em;

    /** @var \wsTests\DataMapper\Model\Friend  */
    public $friend;

    /** @var \wsTests\DataMapper\Model\Contact */
    public $contact;
    
    // +----------------------------------------------------------------------+
    function setUp()
    {
        $this->config = 'dsn=mysql:dbname=test_WScore;charset=utf8 username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        Core::set( 'EntityManager', '\wsTests\DataMapper\Mock\EntityManager' );
        $this->query  = Core::get( 'Query' );
        $this->em     = Core::get( 'EntityManager' );
        $this->friend = Core::get( '\wsTests\DataMapper\Model\Friend' );
        $this->contact= Core::get( '\wsTests\DataMapper\Model\Contact' );
        class_exists( '\wsTests\DataMapper\SetUp' );
        class_exists( '\WScore\DbAccess\Relation' );
        class_exists( '\WScore\DbAccess\Relation_HasOne' );
        $this->setupFriend();
        $this->setupContact();
    }

    /**
     * @param string $table
     * @param int $max
     * @return void
     */
    function setupFriend( $table='mapFriend', $max=3 )
    {
        $this->query->execSQL( SetUp::clearFriend( $table ) );
        $this->query->execSQL( SetUp::setupFriend( $table ) );
        for( $idx = 0; $idx < $max; $idx ++ ) {
            $data = SetUp::makeFriend( $idx );
            $this->query->table( $table )->insert( $data );
        }
    }

    /**
     * @param string $table
     * @param int $max
     */
    function setupContact( $table='mapContact', $max=3 )
    {
        $this->query->execSQL( SetUp::clearContact( $table ) );
        $this->query->execSQL( SetUp::setupContact( $table ) );
        for( $idx = 0; $idx < $max; $idx ++ ) {
            $data = SetUp::makeContact( $idx );
            $this->query->table( $table )->insert( $data );
        }
    }
    // +----------------------------------------------------------------------+
    function test_em_getEntity_gets_an_entity()
    {
        $idx    = 1;
        $friend = $this->em->getEntityFromModel( 'Friend', $idx );
        $this->assertEquals( 'wsTests\DataMapper\Entity\Friend', get_class( $friend ) );
        $this->assertEquals( $idx, $friend->_get_Id() );
        $this->assertEquals( 'Friend', $friend->_get_Model() );
    }
    function test_em_saves_existing_entity_to_db()
    {
        $idx    = 2;
        /** @var $friend Entity\Friend */
        $friend = $this->em->getEntityFromModel( 'Friend', $idx );
        $friend->friend_name = 'my good friend';
        $this->em->save();

        /** @var $friend2 Entity\Friend */
        $friend2 = $this->em->getEntityFromModel( 'Friend', $idx );
        $this->assertEquals( $friend->friend_name, $friend2->friend_name );
    }
    function test_em_newEntity_saves_to_db()
    {
        /** @var $friend Entity\Friend */
        $friend = $this->em->newEntityFromModel( 'Friend' );
        $friend->friend_name = 'my real friend';
        $friend->friend_bday = '1989-01-31';
        $this->em->save();
        $id1 = $friend->_get_Id();
        $this->assertEquals( '4', $id1 );
    }
    function test_em_register_entity_from_select()
    {
        $friend = $this->friend->find(1);
        $this->assertEquals( 'wsTests\DataMapper\Entity\Friend', get_class( $friend ) );
        $this->em->register( $friend );
        $registeredEntities = $this->em->returnEntities();
        $this->assertEquals( 1, count( $registeredEntities ) );
        $this->assertSame( $friend, $registeredEntities['Friend.get.1'] );
    }
    function test_em_registering_many_entities()
    {
        $friends = $this->friend->query()->select();
        $this->assertTrue( is_array( $friends ) );
        $this->assertEquals( 3, count( $friends ) );
        
        $this->em->register( $friends );
        $registeredEntities = $this->em->returnEntities();
        $this->assertEquals( 3, count( $registeredEntities ) );
        $this->assertSame( $friends[0], $registeredEntities['Friend.get.1'] );
    }
    function test_basic_contact_model_and_entity()
    {
        /** @var $contact Entity\Contact */
        /** @var $contact2 Entity\Contact */
        /** @var $contact3 Entity\Contact */
        $contact = $this->contact->find(1);
        $this->assertEquals( 1, $contact->contact_id );
        $contact->friend_id = 10;
        $this->em->register( $contact );
        $this->em->save();

        $contact2 = $this->contact->find(1);
        $this->assertEquals( 1, $contact2->contact_id );
        $this->assertEquals( 10, $contact2->friend_id );

        $contact3 = $this->em->newEntityFromModel( 'Contact' );
        $contact3->contact_info = 'this is new contact';
        $contact3->friend_id    = 15;
        $this->em->save();

        $contact2 = $this->contact->find(4);
        $this->assertEquals( 4, $contact2->contact_id );
        $this->assertEquals( 15, $contact2->friend_id );
        $this->assertEquals( 'this is new contact', $contact2->contact_info );
    }
    function test_relation_hasOne_contact_to_friend()
    {
        $contact = $this->contact->find(1);
        $friend = $this->friend->find(1);
        $this->em->relation( $contact, 'friend' )->set( $friend );
    }
    // +----------------------------------------------------------------------+
}
