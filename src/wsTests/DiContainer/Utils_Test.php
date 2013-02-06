<?php
namespace wsTests\DiContainer;
use \WScore\DiContainer\Utils as Utils;

require_once( __DIR__ . '/../../autoloader.php' );

class Utils_Test extends \PHPUnit_Framework_TestCase
{
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
