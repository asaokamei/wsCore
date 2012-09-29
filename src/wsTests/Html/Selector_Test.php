<?php
namespace wsTests\Html;
require( __DIR__ . '/../../autoloader.php' );
use \wsCore\Core;

Core::go();
/** @var $selector \wsCore\Html\Selector */
$selector = Core::get( '\wsCore\Html\Selector' );

// test Selector_Text

$sel = $selector->getInstance( 'text', 'test' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', 'text <b>bold</b> output' ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', 'text <b>bold</b> output' ) . "\n";

// test Selector_Textarea

$sel = $selector->getInstance( 'textarea', 'test' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', "text <b>bold</b>\n output" ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', "text <b>bold</b>\n output" ) . "\n";

// test Selector_Hidden

$sel = $selector->getInstance( 'hidden', 'test' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', "text <b>bold</b>\n output" ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', "text <b>bold</b>\n output" ) . "\n";

// test Selector_Mail

$sel = $selector->getInstance( 'mail', 'test' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', "text <b>bold</b>\n output" ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', "text <b>bold</b>\n output" ) . "\n";

// test Selector_SelYMD

$sel = $selector->getInstance( 'dateYMD', 'test', 'start_y:1980' );

echo 'class: ' . get_class( $sel ) . "\n";
echo 'html: ' . $sel->popHtml( 'name', "1984-03-31" ) . "\n";
echo 'form: ' . $sel->popHtml( 'form', "1984-03-31" ) . "\n";

