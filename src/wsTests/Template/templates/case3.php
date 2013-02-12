<?php
$_v->parent( __DIR__ . '/layout.php' );
$_v->blockname = 'block name';
$_v->block = $_v->render( __DIR__ . '/block.php' );
?>
test:<?php echo $_v->test;?>
