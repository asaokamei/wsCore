<?php
/** @var $__v \wsModule\Templates\Pure\Template */
$__v->parent( 'tmpLayout.php' ); 
?>
<?php $__v->title = 'template <i>sample</i>'; ?>
This is sample content. <br />
Hello <?= $__v->name; ?>!<br />
<h3>sample foreach</h3>
<ul>
    <?php foreach( $__v->arr( 'bad' ) as $td ) { ?>
    <li><?= $td['name']; ?></li>
    <?php } ?>
</ul>
<p>there should be nothing above and no error.</p>
<h3>XSS: safe html </h3>
<p>show html: <?= $__v->get( 'html' ); ?></p>
<p>show safe: <?= $__v->html; ?></p>