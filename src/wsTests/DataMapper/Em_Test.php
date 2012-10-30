<?php
namespace wsTests\Mapper;

require_once( __DIR__ . '/../../autoloader.php' );
use \wsCore\Core;

class Em_Test extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    public $config;

    /** @var \wsCore\DataMapper\EntityManager */
    public $em;

    /** @var \wsTests\DataMapper\Model\Friend  */
    public $friend;

    function setUp()
    {
        $this->config = 'dsn=mysql:dbname=test_wsCore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        $this->em     = Core::get( '\wsCore\DataMapper\EntityManager' );
        $this->friend = Core::get( '\wsTests\DataMapper\Model\Friend' );
    }
    function test_1()
    {
        $friend = $this->friend->getRecord();
        $this->assertEquals( 'wsTests\DataMapper\Entity\Friend', get_class( $friend ) );

        $this->em->register( $friend );
        $id = $this->em->getEntityProperty( $friend, 'id' );
        $this->assertEquals( 1, $id );
    }
}
