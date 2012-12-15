<?php
namespace wsTests\DiContainer;
use \WScore\DiContainer\Dimplet as Dimplet;

require_once( __DIR__ . '/../../autoloader.php' );

class Dimple_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \WScore\DiContainer\Dimplet */
    var $container;
    
    public function setUp() 
    {
        $this->container = new Dimplet();
    }

    /**
     * @param string $text
     * @return callable
     */
    public function getMaker( $text='test closure maker' )
    {
        return function($c) use( $text ) {
            $obj = new \stdClass();
            $obj->name = $text;
            return $obj;
        };
    }
    // +----------------------------------------------------------------------+
    public function test_id_chain_is_not_implemented()
    {
        // id chain is disabled as of 2012/09/23.
        $id1 = 'id1';
        $id2 = 'id2';
        $id3 = 'id3';
        $this->container->set( $id1, $id2 );
        $this->container->set( $id2, $id3 );
        $got = $this->container->get( $id1 );
        
        // id is NOT chained. so id1 -> id2 only. 
        $this->assertEquals( $id2, $got );
        // certainly not id3...
        $this->assertNotEquals( $id3, $got );
    }

    /**
     * TODO: rewrite this test. Dimplet does not chain id anymore, extend only applied once. 
     */
    public function test_extend_many_classes()
    {
        // call class1 -> class2 
        $mock1 = 'wsTests\DiContainer\DimpleMockBiz\Invoice';

        // extend twice. 
        $text1 = 'extend #1';
        $text2 = 'extend #2';
        $func1 = function( $obj ) use( $text1 ) {
            $obj->extended1 = $text1;
        };
        $func2 = function( $obj ) use( $text2 ) {
            $obj->extended2 = $text2;
        };
        $this->container->extend( $mock1, $func1 );
        $this->container->extend( $mock1, $func2 );
        $mock  = $this->container->get( $mock1 );
        $this->assertTrue( is_object( $mock ) );
        $this->assertEquals( $mock1, get_class( $mock ) );
        $this->assertEquals( $text1, $mock->extended1 );
        $this->assertEquals( $text2, $mock->extended2 );
   }
    public function test_extend_classes()
    {
        // just created an object. 
        $mock1 = 'wsTests\DiContainer\DimpleMockBiz\Invoice';
        $text = 'extended text';
        $extend = function( $obj ) use( $text ) {
            $obj->extended = $text;
        };
        $this->container->extend( $mock1, $extend );
        $mock  = $this->container->get( $mock1 );
        $this->assertTrue( is_object( $mock ) );
        $this->assertEquals( $mock1, get_class( $mock ) );
        $this->assertEquals( $text, $mock->extended );
    }
    public function test_call_classes()
    {
        // call class1 -> class2 
        $mock1 = 'wsTests\DiContainer\DimpleMockBiz\Invoice';
        $mock2 = 'wsTests\DiContainer\DimpleMockDb\DbAccess';
        $this->container->set( $mock1, $mock2 );
        $mock  = $this->container->get( $mock1 );
        $this->assertTrue( is_object( $mock ) );
        $this->assertEquals( $mock2, get_class( $mock ) );
        
        // call easy -> class1 -> class2
        $easy = 'inv';
        $this->container->set( $easy, $mock1 );
        $mock  = $this->container->get( $mock1 );
        $this->assertTrue( is_object( $mock ) );
        $this->assertEquals( $mock2, get_class( $mock ) );
    }
    /**
     *
     */
    public function test_protect_closure()
    {
        // set up closure for testing: maker.
        $text  = 'test closure maker';
        $maker = $this->getMaker( $text );
        $maker = $this->container->protect( $maker ); // protect the maker.
        $this->container->set( 'maker', $maker );
        $func = $this->container->get( 'maker' );
        $this->assertEquals( $maker, $func );
    }
    /**
     *
     */
    public function test_raw_closure()
    {
        // set up closure for testing: maker.
        $text  = 'test closure maker';
        $maker = $this->getMaker( $text );
        $this->container->set( 'maker', $maker );
        $raw = $this->container->raw( 'maker' );

        $this->assertEquals( $maker, $raw );
    }
    /**
     *
     */
    public function test_share_closure()
    {
        // set up closure for testing: maker.
        $text  = 'test closure maker';
        $maker = $this->getMaker( $text );
        $maker = $this->container->share( $maker ); // share the maker.
        $this->container->set( 'maker', $maker );

        // get maker from container.
        $obj = $this->container->get( 'maker' );
        $this->assertEquals( $text, $obj->name );

        // get fresh maker. still identical to obj cos share is used.
        $obj2 = $this->container->fresh( 'maker' );
        $this->assertEquals( $text, $obj2->name );
        $this->assertEquals( $obj, $obj2 );
        $this->assertSame( $obj, $obj2 );
    }

    /**
     *
     */
    public function test_extend_closure()
    {
        // set up closure for testing: maker.
        $text  = 'test closure maker';
        $maker = $this->getMaker( $text );
        $this->container->set( 'maker', $maker );

        // extend the maker. set another attribute.
        $text2 = 'extending maker';
        $extend = function( $maker, $c ) use( $text2 ) {
            $maker->name2 = $text2;
            return $maker;
        };
        $this->container->extend( 'maker', $extend );

        // get maker from container.
        $obj = $this->container->get( 'maker' );
        $this->assertEquals( $text, $obj->name );
        $this->assertEquals( $text2, $obj->name2 );
    }

    /**
     *
     */
    public function test_closure()
    {
        // set up closure for testing: maker.
        $text  = 'test closure maker';
        $maker = $this->getMaker( $text );
        $this->container->set( 'maker', $maker );

        // get maker from container.
        $obj = $this->container->get( 'maker' );
        $this->assertEquals( $text, $obj->name );

        // get fresh maker. they are different object.
        $obj2 = $this->container->fresh( 'maker' );
        $this->assertEquals( $text, $obj2->name );
        $this->assertEquals( $obj, $obj2 );
        $this->assertNotSame( $obj, $obj2 );

        // get existing (i.e. first) maker.
        $obj3 = $this->container->get( 'maker' );
        $this->assertEquals( $obj, $obj3 );
        $this->assertSame( $obj, $obj3 );
    }

    /**
     *
     */
    public function test_get_and_fresh()
    {
        $invoice1 = $this->container->get( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
        $invoice2 = $this->container->fresh( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
        $this->assertTrue( is_object( $invoice1 ) );
        $this->assertTrue( is_object( $invoice2 ) );
        $this->assertEquals( $invoice1, $invoice2 );
        $this->assertNotSame( $invoice1, $invoice2 );

        // get should returns the $invoice1 which is stored in objects. 
        $invoice3 = $this->container->get( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
        $this->assertTrue( is_object( $invoice3 ) );
        $this->assertSame( $invoice1, $invoice3 );
        $this->assertNotSame( $invoice2, $invoice3 );

        // make sure fresh does not stored in the objects; not same with any others. 
        $invoice4 = $this->container->fresh( '\wsTests\DiContainer\DimpleMockBiz\Invoice' );
        $this->assertTrue( is_object( $invoice4 ) );
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