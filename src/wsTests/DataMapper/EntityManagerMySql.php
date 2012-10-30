<?php
namespace wsTests\DataMapper;

require_once( __DIR__ . '/../../autoloader.php' );
use \wsCore\Core;

class EntityManagerMySql extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    public $config;

    /** @var \wsCore\DbAccess\Query */
    public $query;

    /** @var \wsTests\DataMapper\Mock\EntityManager */
    public $em;

    /** @var \wsTests\DataMapper\Model\Friend  */
    public $friend;

    // +----------------------------------------------------------------------+
    function setUp()
    {
        $this->config = 'dsn=mysql:dbname=test_wsCore;charset=utf8 username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        $this->query  = Core::get( 'Query' );
        $this->em     = Core::get( '\wsTests\DataMapper\Mock\EntityManager' );
        $this->friend = Core::get( '\wsTests\DataMapper\Model\Friend' );
        class_exists( '\wsTests\DataMapper\SetUp' );
        $this->setupFriend();
    }
    /**
     * @param string $table
     */
    function setupFriend( $table='mapFriend' )
    {
        $this->query->execSQL( SetUp::clearFriend( $table ) );
        $this->query->execSQL( SetUp::setupFriend( $table ) );
    }
    // +----------------------------------------------------------------------+
    function test_Dao_getRecord_returns_entity()
    {
        $friend = $this->friend->getRecord();
        $this->assertEquals( 'wsTests\DataMapper\Entity\Friend', get_class( $friend ) );
    }
    // +----------------------------------------------------------------------+
}
