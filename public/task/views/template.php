<?php

/** @var $view \wsModule\Alt\Html\View_Bootstrap */
include( __DIR__ . '/../../common/menu/header.php' );

/** @var $view \wsModule\Alt\Html\View_Bootstrap */
$baseUrl = $view->get( 'baseUrl' );

?>
<h4>task/todo demo</h4>
<ul>
    <li><a href="<?php echo $baseUrl; ?>myTasks/setup" >setup</a></li>
</ul>
<?php
echo $view->get( 'content' );

include( __DIR__ . '/../../common/menu/footer.php' );
