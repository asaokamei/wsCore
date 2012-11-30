<?php

/** @var $view \wsModule\Alt\Html\View_Bootstrap */
include( __DIR__ . '/../../common/menu/header.php' );

/** @var $view \wsModule\Alt\Html\View_Bootstrap */
$baseUrl = $view->get( 'baseUrl' );
$appUrl  = $view->get( 'appUrl' );

?>
<style type="text/css">
    ul.subMenu { clear: both; float: right;}
</style>
<ul class="nav nav-pills subMenu">
    <li><a href="<?php echo $appUrl; ?>" >Friends</a></li>
    <li><a href="<?php echo $appUrl; ?>new" >New Friend</a></li>
    <li><a href="<?php echo $appUrl; ?>setup" >setup</a></li>
</ul>
<h4>friend & contact demo</h4>
<p>contact book application for using relation. </p>
<style type="text/css">
div.formListBox li {
    float: left;
    width: 10em;
    list-style: none;
}
</style>
<h1><?= $view->get( 'title' ); ?></h1>
<?php

echo $view->bootstrapShowAlert();

// show contents.
$content = $view->get( 'content' );
if( is_array( $content ) ) $content = implode( '', $content );
echo $content;

// end of html. show fotter.
include( __DIR__ . '/../../common/menu/footer.php' );
