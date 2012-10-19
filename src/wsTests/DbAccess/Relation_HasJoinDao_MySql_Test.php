<?php
namespace wsTests\DbAccess;

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

use \wsCore\Core;
require_once( __DIR__ . '/../../autoloader.php' );

class Relation_HasJoined_MySql_Test extends \PHPUnit_Framework_TestCase
{
    /** @var mixed */
    public $config;

    /** @var \wsCore\DbAccess\Query */
    public $query;

    /** @var Dao_Friend */
    public $friend;

    /** @var Dao_Network */
    public $network;
    // +----------------------------------------------------------------------+
    function setUp()
    {
        $this->config = 'db=mysql dbname=test_wsCore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        $this->query = Core::get( 'Query' );

        $this->setupFriend();
        $this->setupNetwork();

        $this->friend  = Core::get( '\wsTests\DbAccess\Dao_Friend' );
        $this->network = Core::get( '\wsTests\DbAccess\Dao_Network' );

        // load classes before test begins; easier debugging.
        class_exists( '\wsCore\DbAccess\Relation' );
        class_exists( '\wsCore\DbAccess\Relation_HasJoinDao' );
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
    function test_simple_HasJoinDao()
    {
        // create a friend data.
        $id = $this->friend->insert( Dao_SetUp::makeFriend() );
        $friend1 = $this->friend->find( $id );
        $id = $this->friend->insert( Dao_SetUp::makeFriend(1) );
        $friend2 = $this->friend->find( $id );
        $id = $this->friend->insert( Dao_SetUp::makeFriend(2) );
        $friend3 = $this->friend->find( $id );
        $id = $this->friend->insert( Dao_SetUp::makeFriend(3) );
        $friend4 = $this->friend->find( $id );

        // first network from friend1 -> friend2.
        $join  = $friend1->relation( 'network' )->set( $friend2 )->getJoinRecord();
        $join1 = $join[0];
        $join1->set( 'comment', 'first comment' );
        $join1->set( 'status',  1 );
        $join1->update();

        // assert that friend1's good friend is a friend2.
        $friends = $friend1->relation( 'network' )->get();
        $goodFriend = $friends[0];
        $this->assertEquals( $friend2->getId(), $goodFriend->getId() );
        $this->assertEquals( 'first comment', $goodFriend->get( 'comment' ) );
        $this->assertEquals( '1', $goodFriend->get( 'status' ) );

        // add more friends. get them.
        $friend1->relation( 'network' )->set( $friend3 );
        $friends = $friend1->relation( 'network' )->get();
        $goodFriend = $friends[1];
        $this->assertEquals( $friend3->getId(), $goodFriend->getId() );

        // add more friends. in reverse order.
        $friend1->relation( 'network' )->set( $friend3 );
        $friends = $friend1->relation( 'network' )->setOrder( 'network_id DESC' )->get();
        $goodFriend = $friends[0];
        $this->assertEquals( $friend3->getId(), $goodFriend->getId() );
        $goodFriend = $friends[1];
        $this->assertEquals( $friend2->getId(), $goodFriend->getId() );
    }
    // +----------------------------------------------------------------------+
}
