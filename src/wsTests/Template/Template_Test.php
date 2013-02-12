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
        $this->template = $container->fresh( '\wsModule\Templates\Template' );
    }
    public function h( $v ) {
        return htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' );
    }
    // +----------------------------------------------------------------------+
    //  tests on assignments and basic filters.
    // +----------------------------------------------------------------------+
    function test_simple_assignments()
    {
        $t = $this->template;
        $t->set( 'test', 'value' );
        $this->assertEquals( 'value', $t->get( 'test' ) );
        $this->assertEquals( 'value', $t->test );
        $this->assertEquals(  null  , $t->none );
    }
    function test_magic_get_returns_html_safe()
    {
        $word = "<b>bold</b>";
        $t = $this->template;
        $t->set( 'test', $word );
        $this->assertEquals( $word, $t->get( 'test' ) );
        $this->assertEquals( $this->h( $word ), $t->test );
    }
    function test_basic_filters()
    {
        $t = $this->template;
        $word = "<b>bold</b>\nnext line";
        $t->set( 'test', $word );
        $this->assertEquals( $this->h( $word ), $t->get( 'test|h' ) );
        $this->assertEquals( $this->h( $word ), $t->get( 'test|h|none' ) );
        $this->assertEquals( nl2br(    $word ), $t->get( 'test|nl2br' ) );
        $this->assertEquals( nl2br( $this->h( $word ) ), $t->get( 'test|h|nl2br' ) );
        $this->assertEquals( $this->h( $word ), $t->h( 'test' ) );
        $this->assertEquals( nl2br(    $word ), $t->nl2br( 'test' ) );
    }
    function test_arr_returns_empty_array()
    {
        $this->assertEquals( array(), $this->template->arr( 'list' ) );
    }
    function test_arrays()
    {
        $t = $this->template;
        $list = array( 'Jonathan', 'Joester' );
        $t->set( 'list', $list );
        $this->assertEquals( $list, $t->get( 'list' ) );
    }
    function test_true_on_unassigned()
    {
        $this->assertTrue( !$this->template->get( 'list' ) );
    }
    // +----------------------------------------------------------------------+
}
