<?php

/** @var $view \wsModule\Alt\Html\View_Bootstrap */
$baseUrl = ( isset( $view ) ) ? $view->get( 'baseUrl' ) : './' ;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="<?php echo $baseUrl ?>common/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $baseUrl ?>common/css/bootstrap-responsive.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $baseUrl ?>common/css/main.css" />
    <title>WScore Public Demo</title>
</head>
<body>
<div class="container-narrow">
    <div class="masthead">
        <h3 class="muted"><a href="<?php echo $baseUrl ?>index.php" >WScore Public Demo</a></h3>
    </div>
    <hr>
