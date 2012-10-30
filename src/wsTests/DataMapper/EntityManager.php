<?php
namespace wsTests\DataMapper;

require_once( __DIR__ . '/../../autoloader.php' );
use \wsCore\Core;

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
        $this->config = 'dsn=mysql:dbname=test_wsCore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        $this->em     = Core::get( '\wsTests\DataMapper\Mock\EntityManager' );
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
        $friend = $this->friend->getRecord();
        $idEm = $this->em->returnNewId();
        $this->em->register( $friend );
        $id = $this->em->getEntityProperty( $friend, 'id' );
        $this->assertEquals( $idEm, $id );
    }
    function test_new_entity_id_returns_null_if_not_registered()
    {
        $this->em->setupReflection( 'wsTests\DataMapper\Entity\Friend' );
        $friend = $this->friend->getRecord();
        $id = $this->em->getEntityProperty( $friend, 'id' );
        $this->assertEquals( null, $id );
    }
    function test_getModelName()
    {
        $model = $this->em->getModelName( $this->friend );
        $this->assertEquals( 'Friend', $model );

        $model = $this->em->getModelName( 'some\namespace\Friend' );
        $this->assertEquals( 'Friend', $model );

        $model = $this->em->getModelName( 'Friend' );
        $this->assertEquals( 'Friend', $model );
    }
    function test_getModel_from_string()
    {
        $this->em->registerModel( $this->friend );
        $model = $this->em->getModel( 'Friend' );
        $this->assertSame( $this->friend, $model );
    }
    function test_register_model_in_entity_manager()
    {
        $this->em->registerModel( $this->friend );
        $models = $this->em->returnModels();
        $this->assertArrayHasKey( 'Friend', $models );
    }
    function test_em_newEntity_returns_entity()
    {
        $this->em->registerModel( $this->friend );
        $friend = $this->em->newEntity( 'Friend' );
        $this->assertEquals( 'wsTests\DataMapper\Entity\Friend', get_class( $friend ) );

        $friend2 = $this->em->newEntity( 'Friend' );
        $id1 = $friend->_get_Id();
        $id2 = $friend2->_get_Id();
        $this->assertNotEquals( $id1, $id2 );
        $this->assertEquals( $id1+1, $id2 );
    }
    function test_em_register_same_entity_returns_one_entity()
    {
        $this->em->registerModel( $this->friend );
        $friend1 = $this->em->newEntity( 'Friend', 1 );
        $friend2 = $this->em->newEntity( 'Friend', 1 );
        $this->assertNotSame( $friend1, $friend2 );

        $this->em->register( $friend1 );
        $this->em->register( $friend2 );
        $this->assertSame( $friend1, $friend2 );
    }
    // +----------------------------------------------------------------------+
}
