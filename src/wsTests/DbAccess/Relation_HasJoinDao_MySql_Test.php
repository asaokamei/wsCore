<?php
namespace wsTests\DbAccess;

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

use \wsCore\Core;
require_once( __DIR__ . '/../../autoloader.php' );

class Relation_HasJoinDao_MySql_Test extends \PHPUnit_Framework_TestCase
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
        $join  = $this->friend->relation( $friend1, 'network' )->set( $friend2 )->getJoinRecord();
        $join1 = $join[0];
        $join1->comment = 'first comment';
        $join1->status  = 1;
        $this->network->update( $join1->_get_Id(), $join1 );

        // assert that friend1's good friend is a friend2.
        $friends = $friend1->relation( 'network' )->get();
        $goodFriend = $friends[0];
        $this->assertEquals( $friend2->_get_Id(), $goodFriend->_get_Id() );
        $this->assertEquals( 'first comment', $goodFriend->comment );
        $this->assertEquals( '1', $goodFriend->status );

        // add more friends. get them.
        $this->friend->relation( $friend1, 'network' )->setValues( array( 'comment' => '2nd comment', 'status' => 2 ) )->set( $friend3 );
        $friends = $this->friend->relation( $friend1, 'network' )->get();
        $goodFriend2 = $friends[1];
        $this->assertEquals( $friend3->_get_Id(), $goodFriend2->_get_Id() );
        $this->assertEquals( '2nd comment', $goodFriend2->comment );
        $this->assertEquals( '2', $goodFriend2->status );

        // get all friends. in reverse order.
        $friend1->relation( 'network' )->set( $friend3 );
        $friends = $friend1->relation( 'network' )->setOrder( 'network_id DESC' )->get();
        $goodFriend = $friends[0];
        $this->assertEquals( $friend3->_get_Id(), $goodFriend->_get_Id() );
        $goodFriend = $friends[1];
        $this->assertEquals( $friend2->_get_Id(), $goodFriend->_get_Id() );
    }
    // +----------------------------------------------------------------------+
    function test_HasJoinDao_del()
    {
        // create a friend data.
        $id = $this->friend->insert( Dao_SetUp::makeFriend() );
        $friend1 = $this->friend->find( $id );
        $id = $this->friend->insert( Dao_SetUp::makeFriend(1) );
        $friend2 = $this->friend->find( $id );
        $id = $this->friend->insert( Dao_SetUp::makeFriend(2) );
        $friend3 = $this->friend->find( $id );
        // first network from friend1 -> friend2.
        $this->friend->relation( $friend1, 'network' )->set( $friend2 );
        $this->friend->relation( $friend1, 'network' )->set( $friend3 );
        
        // get my network.
        $myNetwork1 = $friend1->relation( 'network' )->get();
        $this->assertEquals( 2, count( $myNetwork1 ) );
        
        // remove one of the network
        $friend1->relation( 'network' )->del( $friend2 );

        // get my network, again.
        $myNetwork2 = $friend1->relation( 'network' )->get();
        $this->assertEquals( 1, count( $myNetwork2 ) );
        $this->assertEquals( $myNetwork1[1]->_get_Id(), $myNetwork2[0]->_get_Id() );
    }
    // +----------------------------------------------------------------------+
}
