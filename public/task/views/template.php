<?php

/** @var $view \wsModule\Alt\Html\View_Bootstrap */
include( __DIR__ . '/../../common/menu/header.php' );

/** @var $view \wsModule\Alt\Html\View_Bootstrap */
$baseUrl = $view->get( 'baseUrl' );
$taskUrl = $view->get( 'taskUrl' );

?>
<style type="text/css">
    ul.subMenu { clear: both; float: right;}
</style>
<ul class="nav nav-pills subMenu">
    <li><a href="<?php echo $taskUrl; ?>" >My Tasks</a></li>
    <li><a href="<?php echo $taskUrl; ?>new" >New Task</a></li>
    <li><a href="<?php echo $taskUrl; ?>setup" >setup</a></li>
</ul>
<h4>task/todo demo</h4>
<p>task/todo application using data mapper and basic MVC. </p>
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
