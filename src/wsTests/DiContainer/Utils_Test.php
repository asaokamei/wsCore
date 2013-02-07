<?php
namespace wsTests\DiContainer;
use \WScore\DiContainer\Utils as Utils;

require_once( __DIR__ . '/../../autoloader.php' );

class Utils_Test extends \PHPUnit_Framework_TestCase
{
    function test_normalizeOption_sample1()
    {
        $option = 'some text';
        $normal = Utils::normalizeOption( $option );
        $this->assertEquals( $option, $normal['construct'][0]['id'] );
    }
    function test_normalizeOption_sample2()
    {
        $option = array( 'some text', 'more text' );
        $normal = Utils::normalizeOption( $option );
        $this->assertEquals( $option[0], $normal['construct'][0]['id'] );
        $this->assertEquals( $option[1], $normal['construct'][1]['id'] );
    }
    function test_normalizeOption_sample3()
    {
        $option = array( 'some text', array( 'more text', 'by'=>'get' ) );
        $normal = Utils::normalizeOption( $option );
        $this->assertEquals( $option[0], $normal['construct'][0]['id'] );
        $this->assertEquals( $option[1][0], $normal['construct'][1]['id'] );
    }
    function test_normalizeOption_sample4()
    {
        $option = array( 'setter' => array( 'some text', 'more text' ) );
        $normal = Utils::normalizeOption( $option );
        $this->assertEquals( $option['setter'][0], $normal['setter'][0]['id'] );
        $this->assertEquals( $option['setter'][1], $normal['setter'][1]['id'] );
    }
    function test_normalizeInfo_no_effect()
    {
        $text = 'normalize test';
        $key  = 'myKey';
        $info = array( $key => array( 'id' => $text, 'key2' => 'value2' ) );
        $norm = Utils::normalizeInjection( $info );
        $this->assertTrue( is_array( $norm ) );
        $this->assertArrayHasKey( 'id', $norm[$key] );
        $this->assertEquals( $text, $norm[$key]['id'] );
        $this->assertEquals( 'value2', $norm[$key]['key2'] );
    }
    function test_normalizeInfo_simple_text()
    {
        $info = 'normalize test';
        $norm = Utils::normalizeInjection( $info );
        $this->assertTrue( is_array( $norm ) );
        $this->assertArrayHasKey( 'id', $norm[0] );
        $this->assertEquals( $info, $norm[0]['id'] );
    }
    function test_normalizeInfo_text_for_key()
    {
        $text = 'normalize test';
        $key  = 'myKey';
        $info = array( $key => $text );
        $norm = Utils::normalizeInjection( $info );
        $this->assertTrue( is_array( $norm ) );
        $this->assertArrayHasKey( 'id', $norm[$key] );
        $this->assertEquals( $text, $norm[$key]['id'] );
    }
    function test_normalizeInfo_text_inside_info()
    {
        $text = 'normalize test';
        $key  = 'myKey';
        $info = array( $key => array( $text, 'key2' => 'value2' ) );
        $norm = Utils::normalizeInjection( $info );
        $this->assertTrue( is_array( $norm ) );
        $this->assertArrayHasKey( 'id', $norm[$key] );
        $this->assertEquals( $text, $norm[$key]['id'] );
        $this->assertEquals( 'value2', $norm[$key]['key2'] );
    }
}
