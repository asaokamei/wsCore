<?php
namespace wsTests\Html;
require( __DIR__ . '/../../autoloader.php' );
use \wsCore\Core;

Core::go();
/** @var $selector \wsCore\Html\Selector */
$selector = Core::get( '\wsCore\Html\Selector' );
$text = $selector->getInstance( 'text', 'test' );
$selector->set( 'test' );

