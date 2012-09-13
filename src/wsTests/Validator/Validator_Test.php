<?php
namespace wsTests\Validator;
use \wsCore\Validator\Validator as Validator;
require_once( __DIR__ . '/../../autoloader.php' );

class Validator_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \wsCore\Validator\Validator */
    var $validator;

    public function setUp()
    {
        $this->validator = new Validator();
    }
    // +----------------------------------------------------------------------+
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
    }
    // +----------------------------------------------------------------------+
}