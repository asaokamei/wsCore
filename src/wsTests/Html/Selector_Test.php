<?php
namespace wsTests\Html;
require( __DIR__ . '/../../autoloader.php' );
use \WScore\Core;

Core::go();
/** @var $selector \WScore\Html\Selector */
$selector = Core::get( '\WScore\Html\Selector' );

/**
 * todo: make PHPUnit tests
 */

// test Selector_Text

$sel = $selector->getInstance( 'text', 'test' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', 'text <strong>bold</strong> output' ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', 'text <strong>bold</strong> output' ) . "\n";

// test Selector_Textarea

$sel = $selector->getInstance( 'textarea', 'test' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', "text <strong>bold</strong>\n output" ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', "text <strong>bold</strong>\n output" ) . "\n";

// test Selector_Hidden

$sel = $selector->getInstance( 'hidden', 'test' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', "text <strong>bold</strong>\n output" ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', "text <strong>bold</strong>\n output" ) . "\n";

// test Selector_Mail

$sel = $selector->getInstance( 'mail', 'test' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', "text <strong>bold</strong>\n output" ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', "text <strong>bold</strong>\n output" ) . "\n";

// test Selector_SelYMD

$sel = $selector->getInstance( 'dateYMD', 'test', 'start_y:1980' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', "1984-03-31" ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', "1984-03-31" ) . "\n";

