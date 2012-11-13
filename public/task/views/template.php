<?php

/** @var $view \wsModule\Alt\Html\View_Bootstrap */
include( __DIR__ . '/../../common/menu/header.php' );

/** @var $view \wsModule\Alt\Html\View_Bootstrap */
$baseUrl = $view->get( 'baseUrl' );

?>
<h4>task/todo demo</h4>
    <p>simple task/todo application for demonstrating data mapper, basic mvc. </p>
    <style type="text/css">
        ul.subMenu { clear: both; float: right;}
        ul.subMenu li { float: left; list-style-type: none; padding: 0 1em; margin: 0 1px; background-color: #eef0f8; }
    </style>
<ul class="subMenu">
    <li><a href="<?php echo $baseUrl; ?>myTasks/" >list tasks</a></li>
    <li><a href="<?php echo $baseUrl; ?>myTasks/new" >new tasks</a></li>
    <li><a href="<?php echo $baseUrl; ?>myTasks/setup" >setup</a></li>
</ul>
<div style="clear:both">
</div>
<h1><?= $view->get( 'title' ); ?></h1>
<?php

echo $view->bootstrapShowAlert();

// show contents.
$content = $view->get( 'content' );
if( is_array( $content ) ) $content = implode( '', $content );
echo $content;

// end of html. show fotter.
include( __DIR__ . '/../../common/menu/footer.php' );
