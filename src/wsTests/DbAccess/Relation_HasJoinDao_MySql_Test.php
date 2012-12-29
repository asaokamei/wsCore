<?php
namespace wsTests\DbAccess;

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

use \WScore\Core;
require_once( __DIR__ . '/../../autoloader.php' );

class Relation_HasJoinDao_MySql_Test extends \PHPUnit_Framework_TestCase
{
    /** @var mixed */
    public $config;

    /** @var \WScore\DbAccess\Query */
    public $query;

    /** @var \WScore\DataMapper\EntityManager */
    public $em;
    
    /** @var Dao_Friend */
    public $friend;

    /** @var Dao_Network */
    public $network;
    // +----------------------------------------------------------------------+
    function setUp()
    {
        $this->config = 'db=mysql dbname=test_WScore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        $this->query = Core::get( 'Query' );

        $this->setupFriend();
        $this->setupNetwork();

        $this->em      = Core::get( 'EntityManager' );
        $this->friend  = Core::get( '\wsTests\DbAccess\Dao_Friend' );
        $this->network = Core::get( '\wsTests\DbAccess\Dao_Network' );

        // load classes before test begins; easier debugging.
        class_exists( '\WScore\DbAccess\Relation' );
        class_exists( '\WScore\DbAccess\Relation_HasJoinDao' );
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
    function setupNetwork( $table='network' )
    {
        $this->query->execSQL( Dao_SetUp::clearNetwork( $table ) );
        $this->query->execSQL( Dao_SetUp::setupNetwork( $table ) );
    }
    // +----------------------------------------------------------------------+
    function test_set_joint_properties()
    {
        // create a friend data.
        $id1 = $this->friend->insert( Dao_SetUp::makeFriend() );
        $friend1 = $this->friend->find( $id1 );
        $id2 = $this->friend->insert( Dao_SetUp::makeFriend(1) );
        $friend2 = $this->friend->find( $id2 );

        // first network from friend1 -> friend2.
        $join1  = $this->friend->relation( $friend1, 'network' )->set( $friend2 );
        // and set some properties in the joint. 
        $join1[ 'comment' ] = 'first comment';
        $join1[ 'status'  ]  = 1;
        $this->em->save();

        // assert that friend1's good friend is a friend2.
        $friend1 = $this->em->getEntity( '\wsTests\DbAccess\Dao_Friend', $id1 );
        $friends = $this->friend->relation( $friend1, 'network' )->get();
        $goodFriend = array_shift( $friends );
        $this->assertEquals( $friend2->_get_Identifier(), $goodFriend->_get_Identifier() );
        // and joint data is propagated into the friend data. 
        $this->assertEquals( 'first comment', $goodFriend->comment );
        $this->assertEquals( '1', $goodFriend->status );
    }
    function test_simple_HasJoinDao()
    {
        // create a friend data.
        $id = $this->friend->insert( Dao_SetUp::makeFriend() );
        $friend1 = $this->friend->find( $id );
        $id = $this->friend->insert( Dao_SetUp::makeFriend(1) );
        $friend2 = $this->friend->find( $id );
        $id = $this->friend->insert( Dao_SetUp::makeFriend(2) );
        $friend3 = $this->friend->find( $id );

        // first network from friend1 -> friend2.
        $relation = $this->friend->relation( $friend1, 'network' );
        $relation->set( $friend2 );
        $relation->set( $friend3 );
        $this->em->save();

        // assert that friend1's good friend is a friend2.
        $friends = $relation->get();
        $goodFriend = array_shift( $friends );
        $this->assertEquals( $friend2->_get_Identifier(), $goodFriend->_get_Identifier() );
        $goodFriend2 = array_shift( $friends );
        $this->assertEquals( $friend3->_get_Identifier(), $goodFriend2->_get_Identifier() );
    }
    // +----------------------------------------------------------------------+
    function test_HasJoinDao_del()
    {
        // create a friend data.
        $id1 = $this->friend->insert( Dao_SetUp::makeFriend() );
        $friend1 = $this->friend->find( $id1 );
        $id = $this->friend->insert( Dao_SetUp::makeFriend(1) );
        $friend2 = $this->friend->find( $id );
        $id = $this->friend->insert( Dao_SetUp::makeFriend(2) );
        $friend3 = $this->friend->find( $id );
        // first network from friend1 -> friend2.
        $relation1 = $this->friend->relation( $friend1, 'network' );
        $relation1->set( $friend2 );
        $relation1->set( $friend3 );
        $this->em->save();
        
        // get my network.
        $myNetwork1 = $relation1->get();
        $this->assertEquals( 2, count( $myNetwork1 ) );
        
        // remove one of the network
        $relation1->del( $friend2 );
        $this->em->save();

        // check my network in the relation object.
        $myNetwork2 = $relation1->get();
        $this->assertEquals( 1, count( $myNetwork2 ) );
        $goodFriend = array_shift( $myNetwork2 );
        $this->assertEquals( $friend3->_get_Identifier(), $goodFriend->_get_Identifier() );

        // read data from database again, and do the same check. 
        $friend1 = $this->friend->find( $id1 );
        $relation2 = $this->friend->relation( $friend1, 'network' );
        $myNetwork2 = $relation2->get();
        $this->assertEquals( 1, count( $myNetwork2 ) );
        $goodFriend = array_shift( $myNetwork2 );
        $this->assertEquals( $friend3->_get_Identifier(), $goodFriend->_get_Identifier() );
    }
    // +----------------------------------------------------------------------+
}
