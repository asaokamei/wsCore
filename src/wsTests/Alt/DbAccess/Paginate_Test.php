<?php
namespace wsTests\Alt\DbAccess;

use \WScore\DbAccess\Query;
use \wsModule\Alt\DbAccess\Paginate;

require_once( __DIR__ . '/../../../autoloader.php' );

class Paginate_Test extends \PHPUnit_Framework_TestCase
{
    /** @var \wsModule\Alt\DbAccess\Paginate */
    public $pager;

    /** @var \WScore\DbAccess\Query */
    public $query;
    function setUp()
    {
        $this->pager = new Paginate();
        $this->query = new Query();
    }
    function test_total_30_has_3_pages() {
        $this->pager->per_page = 10;
        $this->pager->setCurrPage( 1 );
        $this->pager->setTotal( 30 );
        $this->pager->calc();
        $this->assertCount( 3, $this->pager->pages );
    }
    function test_total_31_has_4_pages() {
        $this->pager->per_page = 10;
        $this->pager->setCurrPage( 1 );
        $this->pager->setTotal( 31 );
        $this->pager->calc();
        $this->assertCount( 4, $this->pager->pages );
    }
    function test_simple_pagination()
    {
        $this->pager->per_page = 10;
        $this->pager->setCurrPage( 1 );
        $this->pager->setTotal( 25 );
        $this->pager->calc();
        $this->assertNull(        $this->pager->top_page );
        $this->assertNull(        $this->pager->prev_page );
        $this->assertNull(        $this->pager->last_page );
        $this->assertEquals(  2,  $this->pager->next_page );
        $this->assertCount(   3,  $this->pager->pages );
        $this->assertEquals( '',  $this->pager->pages[1] );
        $this->assertEquals( '2', $this->pager->pages[2] );
        $this->assertEquals( '3', $this->pager->pages[3] );
    }
    function test_some_pagination()
    {
        $this->pager->per_page = 10;
        $this->pager->setCurrPage( 3 );
        $this->pager->setTotal( 100 );
        $this->pager->calc();
        $this->assertNull(        $this->pager->top_page );
        $this->assertEquals(  2,  $this->pager->prev_page );
        $this->assertEquals( 10,  $this->pager->last_page );
        $this->assertEquals(  4,  $this->pager->next_page );
        $this->assertCount(   8,  $this->pager->pages );
        $this->assertEquals( '1', $this->pager->pages[1] );
        $this->assertEquals( '2', $this->pager->pages[2] );
        $this->assertEquals( '',  $this->pager->pages[3] );
        $this->assertEquals( '4', $this->pager->pages[4] );
    }
    function test_more_pagination()
    {
        $this->pager->per_page = 10;
        $this->pager->num_links = 3;
        $this->pager->setCurrPage( 5 );
        $this->pager->setTotal( 100 );
        $this->pager->calc();
        $this->assertEquals(  1,  $this->pager->top_page );
        $this->assertEquals(  4,  $this->pager->prev_page );
        $this->assertEquals( 10,  $this->pager->last_page );
        $this->assertEquals(  6,  $this->pager->next_page );
        $this->assertCount(   7,  $this->pager->pages );
        $this->assertEquals( '2', $this->pager->pages[2] );
        $this->assertEquals( '3', $this->pager->pages[3] );
        $this->assertEquals( '4', $this->pager->pages[4] );
        $this->assertEquals( '',  $this->pager->pages[5] );
        $this->assertEquals( '6', $this->pager->pages[6] );
        $this->assertEquals( '7', $this->pager->pages[7] );
        $this->assertEquals( '8', $this->pager->pages[8] );
    }
}
