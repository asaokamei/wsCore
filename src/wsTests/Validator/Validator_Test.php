<?php
namespace wsTests\Validator;
use \WScore\Validator\Validator as Validator;
require_once( __DIR__ . '/../../autoloader.php' );

class Validator_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \WScore\Validator\Validator */
    var $validator;

    public function setUp()
    {
        $this->validator = new Validator();
    }
    // +----------------------------------------------------------------------+
    public function test_false_on_noNull()
    {
        $text_with_null   = "this is \0 with NULL";
        $text_to_validate = $text_with_null;
        $ok = $this->validator->isValid( $text_to_validate );

        // null is quietly removed from text.
        // this specification may change in the future, though.
        $this->assertTrue( $ok );
        $this->assertNotEquals( $text_with_null, $text_to_validate );
        $text_removed = str_replace( "\0", '', $text_with_null );
        $this->assertEquals( $text_removed, $text_to_validate );

        // validate again, but not replacing nulls.
        $text_to_validate = $text_with_null;
        $ok = $this->validator->isValid( $text_to_validate, 'noNull:FALSE' );
        $this->assertTrue( $ok );
        $this->assertEquals( $text_with_null, $text_to_validate );
    }
    public function test_basic_pattern_with_empty_value()
    {
        $text_number = '';
        $text = $text_number;
        $filters = 'pattern:number';

        $ok = $this->validator->isValid( $text, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text_number, $text );
    }
    public function test_basic_pattern()
    {
        $text_number = '123490';
        $text = $text_number;
        $filters = 'pattern:number';

        $ok = $this->validator->isValid( $text, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text_number, $text );
    }
    /**
     *
     */
    public function test_isValid_with_textFilters()
    {
        // run basic check.
        $text = 'this is OK';
        $text2 = $text;
        $filters = 'noNull|encoding:UTF-8|trim';
        $ok = $this->validator->isValid( $text2, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text, $text2 );

        // filter to upper letters.
        $text3 = $text;
        $filters = 'string:upper';
        $ok = $this->validator->isValid( $text2, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( strtoupper( $text ), $text2 );

        // add spaces to filter.
        $text3 = $text . ' ';
        $filters = 'noNull | encoding:UTF-8 | trim | string:upper';
        $ok = $this->validator->isValid( $text2, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( strtoupper( $text ), $text2 );
    }

    /**
     *
     */
    public function test_default_value()
    {
        $text = '';
        $text2 = $text;
        $filters = array(
            'default' => 'set to default',
        );
        $ok = $this->validator->validate( $text2, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $filters['default'], $text2 );

        // check for value 0.
        $text = '0';
        $text2 = $text;
        $filters = array(
            'default' => 'set to default',
        );
        $ok = $this->validator->validate( $text2, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text, $text2 );
    }
    public function test_validating_array_text()
    {
        // filter to upper letters. 
        $texts = array(
            'key1' => '1st text',
            'key2' => '2nd text'
        );
        $text2 = $texts;
        $filters = array(
            'string' => 'upper'
        );
        $ok = $this->validator->validate( $text2, $filters );
        $this->assertTrue( $ok );
        
        foreach( $texts as $key => $tx ) {
            $this->assertEquals( strtoupper( $tx ), $text2[$key] );
        }
    }
    /**
     *
     */
    public function test_noNull_text()
    {
        $text_with_null   = "this is \0 with NULL";
        $text_to_validate = $text_with_null;
        $ok = $this->validator->isValid( $text_to_validate );

        // null is quietly removed from text.
        // this specification may change in the future, though.
        $this->assertTrue( $ok );
        $this->assertNotEquals( $text_with_null, $text_to_validate );
        $text_removed = str_replace( "\0", '', $text_with_null );
        $this->assertEquals( $text_removed, $text_to_validate );
    }

    /**
     *
     */
    public function test_validating_text_with_filters()
    {
        // run basic check. 
        $text = 'this is OK';
        $text2 = $text;
        $filters = array(
            'noNull' => TRUE,
            'encoding' => 'UTF-8',
            'trim' => TRUE
        );
        $ok = $this->validator->_validate( $text2, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( $text, $text2 );
        
        // filter to upper letters. 
        $text3 = $text;
        $filters = array(
            'string' => 'upper'
        );
        $ok = $this->validator->_validate( $text2, $filters );
        $this->assertTrue( $ok );
        $this->assertEquals( strtoupper( $text ), $text2 );
    }
    // +----------------------------------------------------------------------+
}