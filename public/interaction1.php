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

/** @var $entity \wsCore\DbAccess\Context_RoleInput */
$entity = $view->get( 'entity' );

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
        select { width:auto;}
        .formError { color: red; margin-left: 10px; }
    </style>
    <script type="text/javascript" src="./common/js/bootstrap.js"></script>
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
    <?php
    if( !$entity->isValid() ) echo '
      <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">×</button>
        check the input!
      </div>';
    ?>
    <form name="password" method="post" action="interaction1.php?action=<?php echo $view->get( 'action' ); ?>">
        <dl>
            <?php
            $properties = array( 'friend_name', 'friend_bday' );
            foreach( $properties as $prop ) {
            ?>
            <dt><?php echo $entity->popName( $prop ); ?></dt>
            <dd><?php echo $entity->popHtml( $prop ); ?>
            <?php if( $err = $entity->popError( $prop ) ) echo " <span class='formError' >{$err}</span>"; ?></dd>
            <?php } ?>
        </dl>
        <input type="submit" name="interAction" class="btn btn-primary" value="<?php echo $view->get( 'action' ); ?>">
    </form>
    <?php var_dump( $entity->retrieve() ); ?>
    <footer class="footer">
        <hr>
        <p>WScore Developed by WorkSpot.JP<br />
            thanks, bootstrap. </p>
    </footer>
</div>
</body>
</html>