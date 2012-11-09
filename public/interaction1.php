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

/** @var $view \Interaction\view */
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
        div.formListBox { overflow: auto; }
        div.formListBox li { float: left; list-style: none; margin-right: 1.5em; }
    </style>
</head>
<body>
<script type="text/javascript">

</script>
<div class="container-narrow">
    <div class="masthead">
        <h3 class="muted"><a href="index.php" >WScore Public Demo</a></h3>
    </div>
    <hr>
    <h3>Interaction demo#1</h3>
    <p>Interaction with simple steps for inserting a data. The steps go through form -> confirm -> insert. </p>
    <h1><?php echo $view->get( 'title' ); ?></h1>
    <?php
    echo $view->bootstrapAlertError();
    echo $view->bootstrapAlertInfo();
    echo $view->bootstrapAlertSuccess();
    ?>
    <form name="password" method="post" action="interaction1.php?action=<?php echo $view->get( 'action' ); ?>">
        <dl>
            <?php
            $properties = array( 'friend_name', 'friend_gender', 'friend_bday' );
            foreach( $properties as $prop ) {
            ?>
            <dt><?php echo $entity->popName( $prop ); ?></dt>
            <dd><?php echo $entity->popHtml( $prop ); ?>
            <?php if( $err = $entity->popError( $prop ) ) echo " <span class='formError'>&lt;{$err}&gt;</span>"; ?></dd>
            <?php } ?>
        </dl>
        <?php echo $view->getHiddenTag( \wsCore\Web\Session::TOKEN_NAME ); ?>
        <?php echo $view->bootstrapButtonPrimary( 'button-primary' ); ?>
        <?php echo $view->bootstrapButtonSub( 'button-sub' ); ?>
    </form>
    <?php if( $view->get( 'currAction' ) == 'done' ) { ?>
    <div style="text-align: center;">
        <button type="button" class="btn btn-primary" onclick="location.href='index.php'">back to main demo page</button>
    </div>
    <?php } ?>
    <footer class="footer">
        <hr>
        <p>WScore Developed by WorkSpot.JP<br />
            thanks, bootstrap. </p>
    </footer>
</div>
<script type="text/javascript" src="./common/js/jQuery.js"></script>
<script type="text/javascript" src="./common/js/bootstrap.js"></script>
<script type="text/javascript">
    $(".alert").alert();
    $('.nav-tabs').button();
</script>
</body>
</html>