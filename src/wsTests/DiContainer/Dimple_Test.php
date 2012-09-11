<?php
namespace wsTests\DiContainer;
use \wsCore\DiContainer\Dimplet as Dimplet;

require_once( __DIR__ . '/../../autoloader.php' );

class Dimple_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \wsCore\DiContainer\Dimplet */
    var $container;
    
    public function setUp() 
    {
        $this->container = new Dimplet();
    }

    public function test_get_and_fresh()
    {
        $invoice1 = $this->container->get( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
        $invoice2 = $this->container->fresh( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
        $this->assertEquals( $invoice1, $invoice2 );
        $this->assertNotSame( $invoice1, $invoice2 );

        // get should returns the $invoice1 which is stored in objects. 
        $invoice3 = $this->container->get( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
        $this->assertSame( $invoice1, $invoice3 );
        $this->assertNotSame( $invoice2, $invoice3 );

        // make sure fresh does not stored in the objects; not same with any others. 
        $invoice4 = $this->container->fresh( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
        $this->assertNotSame( $invoice3, $invoice4 );
        $this->assertNotSame( $invoice2, $invoice4 );
    }
    /**
     * 
     */
    public function test_check_injection()
    {
        $invoice = $this->container->fresh( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
        $who_inv = $invoice->showDbType();
        $dba = new DimpleMockDb\DbAccess();
        $who_dba = $dba->name;
        
        $this->assertEquals( $who_dba, $who_inv );
    }
}