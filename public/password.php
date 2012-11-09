<?php
require_once( __DIR__ . '/../src/autoloader.php' );
require_once( __DIR__ . '/password/config.php' );
use wsCore\Core;
use wsCore\Utilities\Tools;
use wsCore\Html\Form;

Core::go();
/** @var $page \wsCore\Web\PageMC */
$page = Core::get( '\wsCore\Web\PageMC' );
/** @var $view \wsCore\Html\PageView */
$view = Core::get( '\wsCore\Html\PageView' );
$page->setController( Core::get( '\password\controller' ) );
$act = $page->getActFromPost();

$page->run( $act, $view );

?>
<?php include( './common/menu/header.php' ); ?>
    <h1>generate password</h1>
    <p>specify length of password, check to use symbols (!@#$ etc.), <br />and click generate password button. </p>
    <form name="password" method="post" action="password.php">
        <dl class="dl-horizontal">
            <dt>length of password</dt>
            <dd><?php echo $view->get( 'length' ); ?><br />
            (minimum length of password is 5)
            </dd>
            <dt>use symbols</dt>
            <dd><label><?php echo $view->get( 'symbol' ); ?> check if you want password to have some symbols. </label></dd>
            <dt>get # of passwords</dt>
            <dd><?php echo $view->get( 'count' ); ?></dd>
        </dl>
        <input type="hidden" name="_act" value="generate">
        <input type="submit" name="generate" class="btn btn-primary" value="generate password">
    </form>
    <?php if( $passwords = $view->get( 'passwords' ) ) var_dump( $passwords ); ?>
<?php include( './common/menu/footer.php' ); ?>