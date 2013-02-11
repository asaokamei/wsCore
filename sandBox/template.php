<?php 
require_once( __DIR__ . '/../src/autoloader.php' );

$t = new \wsModule\Templates\Template( 'tmp.php' );
$t->name = 'World';
$t->html = 'this is <b>bold</b> text. ';
$t->date = '1989-03-04';
$t->list = array( 'Jonathan', 'Joaster' );

echo $t;
