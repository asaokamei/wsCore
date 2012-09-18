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