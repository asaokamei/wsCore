<?php $view->parent( 'tmpLayout.php' ); ?>
<?php $view->title = 'template sample'; ?>
This is sample content. <br />
Hello <?= $view->name; ?>!<br />
<h3>sample foreach</h3>
<?php foreach( $view->arr( 'bad' ) as $td ) { ?>
    <li><?= $td['name']; ?></li>
<?php } ?>