<?php 
require_once( __DIR__ . '/../src/wsModule/Templates/Pure/Template.php' );

$t = new \wsModule\Templates\Pure\Template( 'tmp.php' );
$t->name = 'World';

echo $t;
