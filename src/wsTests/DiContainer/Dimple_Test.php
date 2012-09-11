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
    
    public function test_1()
    {
        $invoice = $this->container->fresh( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
    }
}