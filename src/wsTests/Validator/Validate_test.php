<?php
namespace wsTests\Validator;

require_once( __DIR__ . '/../../autoloader.php' );
use \WScore\Core;

class Validate_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \WScore\Validator\Validate */
    var $validate;

    public function setUp()
    {
        Core::go();
        $this->validate = Core::get( '\WScore\Validator\Validate' );
    }
    // +----------------------------------------------------------------------+
    function test_missing_required_data()
    {
        $missing = '';
        $filters = array( 'required' => true );
        $ok = $this->validate->validate( $missing, $filters );
        $this->assertFalse( $ok );
        $this->assertFalse( $this->validate->isValid );
        $error = $this->validate->err_msg;
        $this->assertEquals( 'required field', $error );
    }
    function test_missing_required_array_data()
    {
        $missing = array( '1', '', '2' );
        $filters = array( 'required' => true );
        $ok = $this->validate->validate( $missing, $filters );
        $this->assertFalse( $ok );
        $this->assertFalse( $this->validate->isValid );
        $error = $this->validate->err_msg;
        $this->assertFalse( array_key_exists( 0, $error ) );
        $this->assertTrue(  array_key_exists( 1, $error ) );
        $this->assertFalse( array_key_exists( 2, $error ) );
        $this->assertEquals( 'required field', $error[1] );
    }
    function test_error_pattern_array()
    {
        $text = array( '1234', 'text', '5678' );
        $filters    = array( 'pattern' => 'number' );
        $ok = $this->validate->validate( $text, $filters );
        $this->assertFalse( $ok );
        $this->assertFalse( $this->validate->isValid );
        $error = $this->validate->err_msg;
        $this->assertEquals( 'invalid pattern with number', $error[1] );
    }
    function test_error_pattern_reports_option()
    {
        $text = 'text';
        $filters    = array( 'pattern' => 'number' );
        $ok = $this->validate->validate( $text, $filters );
        $this->assertFalse( $ok );
        $this->assertFalse( $this->validate->isValid );
        $error = $this->validate->err_msg;
        $this->assertEquals( 'invalid pattern with number', $error );
    }
    public function test_is_style()
    {
        // text with upper/lower cases. 
        $text_alpha = 'abcABC';
        $text = $text_alpha;

        // convert to lower case
        $filters = array( 'string' => 'lower' );
        $ok = $this->validate->is( $text, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text_alpha, $text );
        $this->assertEquals( strtolower( $text_alpha ), $this->validate->value );

        // convert to upper case
        $filters = array( 'string' => 'upper' );
        $ok = $this->validate->is( $text, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( strtoupper( $text_alpha ), $this->validate->value );

        $text_number = '123490';
        $text = $text_number;
        $filters = array( 'pattern' => 'number' );

        $ok = $this->validate->is( $text, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text_number, $text );

        $text_alpha = 'text';
        $text = $text_alpha;

        $ok = $this->validate->is( $text, $filters );
        $this->assertFalse( $ok );
        $this->assertEquals( $text_alpha, $text );
    }
    public function test_basic_string()
    {
        // text with upper/lower cases. 
        $text_alpha = 'abcABC';
        $text = $text_alpha;

        // convert to lower case
        $filters = array( 'string' => 'lower' );
        $ok = $this->validate->validate( $text, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text_alpha, $text );
        $this->assertEquals( strtolower( $text_alpha ), $this->validate->value );

        // convert to upper case
        $filters = array( 'string' => 'upper' );
        $ok = $this->validate->validate( $text, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( strtoupper( $text_alpha ), $this->validate->value );
    }
    
    public function test_basic_pattern()
    {
        $text_number = '123490';
        $text = $text_number;
        $filters = array( 'pattern' => 'number' );

        $ok = $this->validate->validate( $text, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text_number, $text );

        $text_alpha = 'text';
        $text = $text_alpha;

        $ok = $this->validate->validate( $text, $filters );
        $this->assertFalse( $ok );
        $this->assertEquals( $text_alpha, $text );
    }
}
