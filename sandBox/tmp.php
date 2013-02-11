<?php
/** @var $_v \wsModule\Templates\Pure\Template */
$_v->parent( 'tmpLayout.php' ); 
?>
<?php $_v->title = 'template <i>sample</i>'; ?>
<?php $_v->block =<<<END_BLOCKS
<h3>This is Here Block</h3>
<p>You see this "Hello {$_v->name}!</p>

END_BLOCKS;
?>
This is sample content. <br />
Hello <?= $_v->name; ?>!<br />
<h3>sample foreach</h3>
<ul>
    <?php foreach( $_v->arr( 'bad' ) as $td ) { ?>
    <li><?= $td['name']; ?></li>
    <?php } ?>
</ul>
<?php if( !$_v->bad ) echo '<p>empty bad array.</p>'."\n" ?>
<p>there should be no error.</p>
<h3>XSS: safe html </h3>
<p>show html: <?= $_v->get( 'html' ); ?></p>
<p>show safe: <?= $_v->html; ?></p>
<p>filter: <?= $_v->get( 'html|h' ); ?></p>
<p>date|dot: <?= $_v->date( 'date|dot' ); ?></p>
