<?php $view->parent( 'tmpLayout.php' ); ?>
<?php $view->title = 'template <i>sample</i>'; ?>
This is sample content. <br />
Hello <?= $view->name; ?>!<br />
<h3>sample foreach</h3>
<ul>
    <?php foreach( $view->arr( 'bad' ) as $td ) { ?>
    <li><?= $td['name']; ?></li>
    <?php } ?>
</ul>
<p>there should be nothing above and no error.</p>
<h3>XSS: safe html </h3>
<p>show html: <?= $view->html; ?></p>
<p>show safe: <?= $view->safe( 'html' ); ?></p>