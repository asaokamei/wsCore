<?php
namespace wsTests\Template;

require_once( __DIR__ . '/../../autoloader.php' );
use \WScore\Core;

class Template_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \wsModule\Templates\Template */
    var $template;
    public function setUp()
    {
        $container = Core::go();
        $this->template = $container->get( '\wsModule\Templates\Template' );
    }
    function test_assignments()
    {
        $t = $this->template;
        $t->set( 'test', 'value' );
        $this->assertEquals( 'value', $t->get( 'test' ) );
        $this->assertEquals( 'value', $t->test );
        $this->assertEquals(  null  , $t->none );
    }
}
