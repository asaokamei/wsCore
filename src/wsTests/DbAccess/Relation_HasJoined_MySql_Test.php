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
        $this->setupGroup();
        $this->setupFr2Gr();

        $this->friend  = Core::get( '\wsTests\DbAccess\Dao_Friend' );
        $this->group   = Core::get( '\wsTests\DbAccess\Dao_Group' );

        // load classes before test begins; easier debugging.
        class_exists( '\wsCore\DbAccess\Relation' );
        class_exists( '\wsCore\DbAccess\Relation_HasJoined' );
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
    function setupGroup( $table='myGroup' )
    {
        $this->query->execSQL( Dao_SetUp::clearGroup( $table ) );
        $this->query->execSQL( Dao_SetUp::setupGroup( $table ) );
    }
    /**
     * @param string $table
     */
    function setupFr2Gr( $table='friend2group' )
    {
        $this->query->execSQL( Dao_SetUp::clearFriend2Group( $table ) );
        $this->query->execSQL( Dao_SetUp::setupFriend2Group( $table ) );
    }
    // +----------------------------------------------------------------------+
    function test_simple_HasJoined()
    {
        // create a friend data.
        $dataFriend = Dao_SetUp::makeFriend();
        $id1 = $this->friend->insert( $dataFriend );
        $friend = $this->friend->find( $id1 );

        // create a group with a relation to the friend data.
        $dataGroup = Dao_SetUp::makeGroup();
        $group = $this->group->getRecord( $dataGroup );
        $this->group->relation( $group, 'friend' )->set( $friend );
        $this->group->insert( $group );

        // check if joined table is saved.
        $joined = $this->query->table( 'friend2group' )->w( 'group_code' )->eq( $dataGroup[ 'group_code' ] )->select();
        $this->assertEquals( 1, count( $joined ) );
        $joined = $joined[0];
        $this->assertEquals( $dataGroup[ 'group_code' ], $joined[ 'group_code' ] );
        $this->assertEquals( $id1, $joined[ 'friend_id' ] );

        // get group from friend.
        // but before that, add more groups
        $group = $this->group->getRecord( Dao_SetUp::makeGroup(1) );
        $this->group->insert( $group );
        // now get group.
        $groups = $this->friend->relation( $friend, 'group' )->get();
        $this->assertEquals( 1, count( $groups ) );
        $groups = $groups[0];
        $this->assertEquals( $dataGroup[ 'group_code' ], $groups->group_code );
        $this->assertEquals( $id1, $groups->friend_id );
        
        // relate the new group with the friend.
        $this->friend->relation( $friend, 'group' )->set( $group );
        $manyFriends = $this->friend->find( $id1 );
        $groups = $this->friend->relation( $manyFriends, 'group' )->get();
        $this->assertEquals( 2, count( $groups ) );
        $group = $groups[0];
        $dataGroup = Dao_SetUp::makeGroup(0);
        $this->assertEquals( $dataGroup[ 'group_code' ], $group->group_code );
        $this->assertEquals( $id1, $group->friend_id );
        $group = $groups[1];
        $dataGroup = Dao_SetUp::makeGroup(1);
        $this->assertEquals( $dataGroup[ 'group_code' ], $group->group_code );
        $this->assertEquals( $id1, $group->friend_id );
        
    }
    function test_more_HasJoined()
    {
        // set up friend and groups. 
        $idFriend = $this->friend->insert( Dao_SetUp::makeFriend() );
        $friend   = $this->friend->find( $idFriend );
        $idGroup1 = $this->group->insert( Dao_SetUp::makeGroup() );
        $idGroup2 = $this->group->insert( Dao_SetUp::makeGroup(1) );
        $group1   = $this->group->find( $idGroup1 );
        $group2   = $this->group->find( $idGroup2 );
        $this->friend->relation( $friend, 'group' )->setValues( array( 'created_date' => '1999-12-31' ) )->set( $group1 );
        $this->friend->relation( $friend, 'group' )->set( $group2 );
        
        // get groups using relations. 
        $groups = $this->friend->relation( $friend, 'group' )->setOrder( 'myGroup.group_code DESC' )->get();
        $group = $groups[1];
        $dataGroup = Dao_SetUp::makeGroup(0);
        $this->assertEquals( $dataGroup[ 'group_code' ], $group->group_code );
        $this->assertEquals( '1999-12-31', $group->created_date );
        $this->assertEquals( $idFriend, $group->friend_id );
        $group = $groups[0];
        $dataGroup = Dao_SetUp::makeGroup(1);
        $this->assertEquals( $dataGroup[ 'group_code' ], $group->group_code );
        $this->assertEquals( '1999-12-31', $group->created_date );
        $this->assertEquals( $idFriend, $group->friend_id );
    }
    function test_HasJoined_del()
    {
        // set up friend and groups. 
        $idFriend = $this->friend->insert( Dao_SetUp::makeFriend() );
        $friend   = $this->friend->find( $idFriend );
        $idGroup1 = $this->group->insert( Dao_SetUp::makeGroup() );
        $idGroup2 = $this->group->insert( Dao_SetUp::makeGroup(1) );
        $group1   = $this->group->find( $idGroup1 );
        $group2   = $this->group->find( $idGroup2 );
        $this->friend->relation( $friend, 'group' )->setValues( array( 'created_date' => '1999-12-31' ) )->set( $group1 );
        $this->friend->relation( $friend, 'group' )->set( $group2 );
        
        // get groups.
        $groups1 = $this->friend->relation( $friend, 'group' )->get();
        $this->assertEquals( 2, count( $groups1 ) );
        
        // delete one of the group.
        $this->friend->relation( $friend, 'group' )->del( $group1 );
        
        // get groups, again. 
        $groups2 = $friend->relation( 'group' )->get();
        $this->assertEquals( 1, count( $groups2 ) );
        $this->assertEquals( $groups1[1]->_get_Id(), $groups2[0]->_get_Id() );
    }
    // +----------------------------------------------------------------------+
}
