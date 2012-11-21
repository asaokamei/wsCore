<?php
namespace wsTests\DataMapper;

require_once( __DIR__ . '/../../autoloader.php' );
use \WScore\Core;

class EntityManager extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    public $config;

    /** @var \wsTests\DataMapper\Mock\EntityManager */
    public $em;

    /** @var \wsTests\DataMapper\Model\Friend  */
    public $friend;

    // +----------------------------------------------------------------------+
    function setUp()
    {
        $this->config = 'dsn=mysql:dbname=test_WScore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        Core::set( 'EntityManager', '\wsTests\DataMapper\Mock\EntityManager' );
        $this->em     = Core::get( 'EntityManager' );
        $this->friend = Core::get( '\wsTests\DataMapper\Model\Friend' );
    }
    // +----------------------------------------------------------------------+
    function test_Dao_getRecord_returns_entity()
    {
        $friend = $this->friend->getRecord();
        $this->assertEquals( 'wsTests\DataMapper\Entity\Friend', get_class( $friend ) );
    }
    function test_Em_registers_new_entity()
    {
        $friend = $this->em->newEntity( 'wsTests\DataMapper\Model\Friend' );
        $id = $friend->_get_Id();
        $this->assertTrue( $id > 0 );
    }
    function test_new_entity_id_returns_null_if_not_registered()
    {
        $this->em->setupReflection( 'wsTests\DataMapper\Entity\Friend' );
        $friend = $this->friend->getRecord();
        $id = $friend->_get_Id();
        $this->assertTrue( $id > 0 );
    }
    function test_getModel_from_string()
    {
        $model = $this->em->getModel( 'wsTests\DataMapper\Model\Friend' );
        $this->assertSame( $this->friend, $model );
    }
    function test_register_model_in_entity_manager()
    {
        $models = $this->em->returnModels();
        $this->assertArrayHasKey( 'wsTests\DataMapper\Model\Friend', $models );
    }
    function test_em_newEntity_returns_entity()
    {
        $friend = $this->em->newEntity( 'wsTests\DataMapper\Model\Friend' );
        $this->assertEquals( 'wsTests\DataMapper\Entity\Friend', get_class( $friend ) );

        $friend2 = $this->em->newEntity( 'wsTests\DataMapper\Model\Friend' );
        $id1 = $friend->_get_Id();
        $id2 = $friend2->_get_Id();
        $this->assertNotEquals( $id1, $id2 );
        $this->assertEquals( $id1+1, $id2 );
    }
    function test_em_register_same_entity_returns_one_entity()
    {
        $friend1 = $this->em->newEntity( 'wsTests\DataMapper\Model\Friend', 1 );
        $friend2 = $this->em->newEntity( 'wsTests\DataMapper\Model\Friend', 1 );
        $this->assertNotSame( $friend1, $friend2 );
    }
    // +----------------------------------------------------------------------+
}
