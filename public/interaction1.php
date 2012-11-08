<?php
require_once( __DIR__ . '/../src/autoloader.php' );
require_once( __DIR__ . '/Interaction/interact.config.php' );
use wsCore\Core;

Core::go();
Core::setPdo( 'dsn=sqlite::memory:' );
$model   = Core::get( 'Interaction\model' );
/** @var $intAct Interaction\interact */
$intAct = Core::get( 'Interaction\interact' );

if( !$action = wsCore\Utilities\Tools::getKey( $_REQUEST, 'action' ) ) {
    $action = 'form';
}
else {
    $intAct->loadRegistered();
}

$view    = Core::get( 'interaction\view' );
$intAct->run( 'insertData', $action, $view );

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="./common/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="./common/css/bootstrap-responsive.css" />
    <link rel="stylesheet" type="text/css" href="./common/css/main.css" />
    <title>WScore Public Demo</title>
    <style type="text/css">
    </style>
</head>
<body>
<div class="container-narrow">
    <div class="masthead">
        <h3 class="muted"><a href="index.php" >WScore Public Demo</a></h3>
    </div>
    <hr>
    <h1>Interaction demo#1</h1>
    <p>Interaction with simple steps for inserting a data. The steps go through form -> confirm -> insert. </p>
    <h3>title: <?php echo $view->get( 'title' ); ?></h3>
    <form name="password" method="post" action="interaction1.php?action=<?php echo $view->get( 'action' ); ?>">
        <dl>
            <dd>error!?</dd>
            <dt><label><input type="checkbox" name="error" value="error" >
            click this checkbox to generate validation error. </label></dt>
        </dl>
        <input type="submit" name="interAction" class="btn btn-primary" value="<?php echo $view->get( 'action' ); ?>">
    </form>
    <?php var_dump( $view->get( 'entity' ) ); ?>
    <footer class="footer">
        <hr>
        <p>WScore Developed by WorkSpot.JP<br />
            thanks, bootstrap. </p>
    </footer>
</div>
</body>
</html>