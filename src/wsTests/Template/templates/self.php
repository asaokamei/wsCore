<?php
use \WScore\Core;

$c = Core::dic();
/** @var $t \wsModule\Templates\Template */
$t = $c->fresh( '\wsModule\Templates\Template' );
$t->test = 'selfTest';
$t->parent( __DIR__ . '/layout.php' );
$t->renderSelf();
?>
test:<?php echo $t->test; ?>
<?php unset( $t ); ?>