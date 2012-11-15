<?php
require_once( __DIR__ . '/../src/autoloader.php' );
require_once( __DIR__ . '/Interaction/config.php' );
use WScore\Core;

Core::go();
Core::setPdo( 'dsn=sqlite::memory:' );
/** @var $model Interaction\model */
/** @var $intAct Interaction\interact */
/** @var $view \Interaction\view2 */
/** @var $entity \WScore\DbAccess\Role_Input */

Core::set( 'interactView', '\Interaction\view2' );
$model  = Core::get( 'Interaction\model' );
$intAct = Core::get( 'Interaction\interact' );
$view = $intAct->action( 'wizard', 'wizard1' );
$entity = $view->get( 'entity' );

?>
<?php include( './common/menu/header.php' ); ?>
<style type="text/css">
    select { width:auto;}
    .formError { color: red; margin-left: 10px; }
    div.formListBox { overflow: auto; }
    div.formListBox li { float: left; list-style: none; margin-right: 1.5em; }
</style>
    <h4>demo #2: interaction like a wizard</h4>
    <p>Wizard like steps to insert a data. steps are: form1 -> form2 -> form3 -> confirm -> save. </p>
    <h1><?php echo $view->get( 'title' ); ?></h1>
    <?php
    echo $view->bootstrapAlertError();
    echo $view->bootstrapAlertInfo();
    echo $view->bootstrapAlertSuccess();
    ?>
    <form name="password" method="post" action="interaction2.php?action=<?php echo $view->get( 'action' ); ?>">
        <dl>
            <?php
            $properties = $model->getPropertyList( $view->get( 'currAction' ) );
            foreach( $properties as $prop => $name ) {
                ?>
                <dt><?php echo $name; ?></dt>
                <dd><?php echo $entity->popHtml( $prop ); ?>
                    <?php if( $err = $entity->popError( $prop ) ) echo " <span class='formError'>&lt;{$err}&gt;</span>"; ?></dd>
                <?php } ?>
        </dl>
        <?php echo $view->getHiddenTag( \WScore\Web\Session::TOKEN_NAME ); ?>
        <div style="float: right; ">
            <?php echo $view->bootstrapButtonPrimary( 'button-primary' ); ?>
        </div>
        <?php echo $view->bootstrapButtonJump( 'button-sub' ); ?>
    </form>
    <?php if( $view->get( 'currAction' ) == 'done' ) { ?>
    <div style="text-align: center;">
        <button type="button" class="btn btn-primary" onclick="location.href='index.php'">back to main demo page</button>
    </div>
    <?php } ?>
    <div style="clear:both"></div>
<script type="text/javascript" src="./common/js/jQuery.js"></script>
<script type="text/javascript" src="./common/js/bootstrap.js"></script>
<script type="text/javascript">
    $(".alert").alert();
    $('.nav-tabs').button();
</script>
<?php include( './common/menu/footer.php' ); ?>