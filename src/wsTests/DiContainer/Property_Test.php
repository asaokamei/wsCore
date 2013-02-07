<?php
namespace wsTests\DiContainer;
use \WScore\DiContainer\Dimplet as Dimplet;
use \WScore\DiContainer\Forge as Forge;

require_once( __DIR__ . '/../../autoloader.php' );

class Property_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \WScore\DiContainer\Dimplet */
    var $container;

    /** @var \WScore\DiContainer\Forge */
    var $forge;

    public function setUp()
    {
        $this->container = new Dimplet();
        $this->forge     = new Forge();
    }
    function test_forge_propertyInjection()
    {
        Forge::$PROPERTY_INJECTION = true;
        /** @var $a \wsTests\DiContainer\DimpletMockProp\A */
        $a = $this->container->get( '\wsTests\DiContainer\DimpletMockProp\A' );
        /** @var $injected \wsTests\DiContainer\DimpleMockDb\DbAccess */
        $injected = $a->getInjected();
        $this->assertEquals( 'dba at test1', $injected->name );
    }
    function test_forge_propertyInjection_B()
    {
        Forge::$PROPERTY_INJECTION = true;
        /** @var $a \wsTests\DiContainer\DimpletMockProp\B */
        $a = $this->container->get( '\wsTests\DiContainer\DimpletMockProp\B' );
        /** @var $injected \wsTests\DiContainer\DimpleMockDb\DbAccess */
        $injected = $a->getInjected();
        $this->assertEquals( 'dumb access', $injected->name );
        $invoice = $a->getInvoice();
        //$this->assertEquals( 'dumb access', $invoice->name );
    }

    function test_dimProperty_override_private_property()
    {
        $class = '\wsTests\DiContainer\DimpletMockProp\C';
        $list  = $this->forge->listDi( $class );
        $this->assertEquals( '\wsTests\DiContainer\DimpleMockDb\DbAccess', $list[ 'property' ][ 'invoice' ][ 'id' ] );
    }
    function test_dimProperty_can_get_child_property()
    {
        $class = '\wsTests\DiContainer\DimpletMockProp\C';
        $list  = $this->forge->listDi( $class );
        $this->assertEquals( '\wsTests\DiContainer\DimpleMockDb\DumbAccess', $list[ 'property' ][ 'injected' ][ 'id' ] );
    }
    function test_dimProperty_override_case()
    {
        $class = '\wsTests\DiContainer\DimpletMockProp\B';
        $list  = $this->forge->listDi( $class );
        $this->assertEquals( '\wsTests\DiContainer\DimpleMockDb\DumbAccess', $list[ 'property' ][ 'injected' ][ 'id' ] );
    }
    function test_dimProperty_simple_case()
    {
        $class = '\wsTests\DiContainer\DimpletMockProp\A';
        $list  = $this->forge->listDi( $class );
        $this->assertEquals( '\wsTests\DiContainer\DimpleMockDb\DbAccess', $list[ 'property' ][ 'injected' ][ 'id' ] );
        $this->assertEquals( '\wsTests\DiContainer\DimpleMockBiz\Invoice', $list[ 'property' ][ 'invoice'  ][ 'id' ] );
    }
}

