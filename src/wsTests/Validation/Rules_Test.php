<?php
namespace wsTests\Validator;

require_once( __DIR__ . '/../../autoloader.php' );
use \WScore\Core;

class Rules_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \WScore\Validator\Rules */
    var $rule;

    public function setUp()
    {
        Core::go();
        $this->rule = Core::get( '\WScore\Validator\Rules' );
    }
    // +----------------------------------------------------------------------+
    function test_merge_filter()
    {
        $this->rule->mergeFilter( 'test:test 1st' );
        $filter = $this->rule->filter;
        $this->assertArrayHasKey( 'test', $filter );
        $this->assertEquals( 'test 1st', $filter[ 'test' ] );
    }
    function test_mail_type()
    {
        $rule2 = $this->rule->mail( 'test:test 1st' );
        
        // mail should return new rule object.
        $this->assertNotSame( $this->rule, $rule2 );
        
        // new filters. 
        $filter = $rule2->filter;
        $this->assertArrayHasKey( 'test', $filter );
        $this->assertEquals( 'test 1st', $filter[ 'test' ] );

        // email filter have sanitize for email. 
        $this->assertArrayHasKey( 'sanitize', $filter );
        $this->assertEquals( 'mail', $filter[ 'sanitize' ] );
        
        // type is email. 
        $this->assertEquals( 'mail', $rule2->getType() );
    }
    function test_date_type()
    {
        // currently, date type should use __call method. 
        $rule2 = $this->rule->date( 'test:test 1st' );

        // mail should return new rule object.
        $this->assertNotSame( $this->rule, $rule2 );

        // new filters. 
        $filter = $rule2->filter;
        $this->assertArrayHasKey( 'test', $filter );
        $this->assertEquals( 'test 1st', $filter[ 'test' ] );

        // date should have multiple type, date.
        $this->assertArrayHasKey( 'multiple', $filter );
        $this->assertEquals( 'YMD', $filter[ 'multiple' ] );
        
        // email filter have sanitize original value, false. 
        $this->assertArrayHasKey( 'sanitize', $filter );
        $this->assertFalse( $filter[ 'sanitize' ] );

        // type is date. 
        $this->assertEquals( 'date', $rule2->getType() );
    }
}