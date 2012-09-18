<?php
namespace wsTests\Validator;
require_once( __DIR__ . '/../../autoloader.php' );

use \wsCore\Validator\DataIO as dio;
use \wsCore\Validator\Validator as validator;

class DataIO_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \wsCore\Validator\DataIO */
    var $dio;
    public function setUp()
    {
        $this->dio = new dio();
        $this->dio->injectValidator( new validator() );
    }
    public function getData()
    {
        return array(
            'email' => 'test@example.com',
            'number' => '123',
        );
    }
    // +----------------------------------------------------------------------+
    public function test_validating_array()
    {
        $input = array( 'num' => array( '1', '2', 'bad', '4' ) );
        $err_msg = 'Not a Number';
        $errors = FALSE;
        $this->dio->source( $input );
        $this->dio->pushValue( 'num', 'pattern:number | err_msg:'.$err_msg );
        // check errors.
        $isError = $this->dio->popErrors( $errors );
        $this->assertTrue( !!$isError );
        $this->assertNotEmpty( $err_msg, $errors['num'][2] );
        $this->assertEquals( $err_msg, $errors['num'][2] );

        // test popData. should have all the values
        $allData = $this->dio->popData();
        $this->assertTrue( isset( $allData['num'][2] ) );

        // test popSafe. should not have value with errors.
        $safeData = $this->dio->popSafe();
        $this->assertFalse( isset( $safeData['num'][2] ) );

    }
    public function test_simple_push_and_pop()
    {
        $data = $this->getData();
        $value = NULL;
        $this->dio->source( $data );
        $this->dio->pushValue( 'number', 'pattern:number', $value );

        $this->assertEquals( $data[ 'number' ], $value );
    }
    // +----------------------------------------------------------------------+
}