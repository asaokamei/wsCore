<?php
namespace wsTests\Mapper;

require_once( __DIR__ . '/../../autoloader.php' );
use \wsCore\Core;

class Em_Test extends \PHPUnit_Framework_TestCase
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
    function test_register_dao_in_entity_manager()
    {
        $this->em->registerDao( $this->friend );
        $models = $this->em->returnModels();
        $this->assertArrayHasKey( 'Friend', $models );
    }
    // +----------------------------------------------------------------------+
}
