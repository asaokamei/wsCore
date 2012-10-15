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
    function test_1()
    {
        $dataFriend = Dao_SetUp::makeFriend();
        $id1 = $this->friend->insert( $dataFriend );
        $friend = $this->friend->find( $id1 );
        
        $dataContact = Dao_SetUp::makeContact();
        $contact = $this->contact->getRecord();
        $contact->load( $dataContact );
        $contact->relation( 'friend_id' )->set( $friend );
        $this->assertEquals( $id1, $contact->get( 'friend_id' ) );
    }
}

    