<?php 
require_once( __DIR__ . '/../src/autoloader.php' );

$t = new \wsModule\Templates\Pure\Template( 'tmp.php' );
$t->name = 'World';
$t->html = 'this is <b>bold</b> text. ';
$t->date = '1989-03-04';

echo $t;
