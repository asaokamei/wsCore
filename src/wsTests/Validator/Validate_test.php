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
    public function test_invoke_style()
    {
        // text with upper/lower cases. 
        $validate = $this->validate;
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

        $ok = $validate( $text, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text_number, $text );

        $text_alpha = 'text';
        $text = $text_alpha;

        $ok = $validate( $text, $filters );
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
