<?php
namespace wsTests\DbAccess;

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
        $group = $this->group->getRecord();
        $group->load( $dataGroup );
        $group->relation( 'friend' )->set( $friend );
        $group->insert();

        // check if joined table is saved.
        $joined = $this->query->table( 'friend2group' )->w( 'group_code' )->eq( $dataGroup[ 'group_code' ] )->select();
        $this->assertEquals( 1, count( $joined ) );
        $joined = $joined[0];
        $this->assertEquals( $dataGroup[ 'group_code' ], $joined[ 'group_code' ] );
        $this->assertEquals( $id1, $joined[ 'friend_id' ] );

        // get group from friend.
        $groups = $friend->relation( 'group' )->get();
        $this->assertEquals( 1, count( $groups ) );
        $groups = $groups[0];
        $this->assertEquals( $dataGroup[ 'group_code' ], $groups[ 'group_code' ] );
        $this->assertEquals( $id1, $groups[ 'friend_id' ] );
    }
    // +----------------------------------------------------------------------+
}
