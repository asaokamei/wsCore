<?php
require_once( __DIR__ . '/../src/autoloader.php' );
require_once( __DIR__ . '/password/config.php' );
use WScore\Core;

Core::go();
/** @var $page \WScore\Web\PageMC */
/** @var $view \WScore\Html\PageView */

$page = Core::get( '\WScore\Web\PageMC' );
$view = Core::get( '\WScore\Html\PageView' );
$page->setController( Core::get( '\password\controller' ) );
$act  = $page->getActFromPost();

$page->run( $act, $view );

?>
<?php include( './common/menu/header.php' ); ?>
    <h1>generate password</h1>
    <p>specify length of password, check to use symbols (!@#$ etc.), <br />and click generate password button. </p>
    <form name="password" method="post" action="password.php">
        <dl class="dl-horizontal">
            <dt>length of password</dt>
            <dd><?php echo $view->get( 'length' ); ?>
            &nbsp;<p class="muted">(minimum length of password is 5)</p>
            </dd>
            <dt>use symbols</dt>
            <dd><label><?php echo $view->get( 'symbol' ); ?> check if you want password to have some symbols. </label></dd>
            <dt>get # of passwords</dt>
            <dd><?php echo $view->get( 'count' ); ?></dd>
        </dl>
        <input type="hidden" name="_act" value="generate">
        <input type="submit" name="generate" class="btn btn-primary" value="generate password">
    </form>
    <?php 
if( $passwords = $view->get( 'passwords' ) ) 
{
    /** @var $tags \WScore\Html\Tags */
    /** @var $table \WScore\Html\Tags */
    $tags = Core::get( '\WScore\Html\Tags' );
    $table = $tags->table()->_class( 'table' )->contain_(
        $tags->tr(
            $tags->th( '#' ),
            $tags->th( 'generated password' )
        )
    );
    $counter = 0;
    foreach( $passwords as $pwd ) {
        $table->contain_(
            $tags->tr(
                $tags->td( ++$counter ),
                $tags->td( $tags->span( $pwd )->style( 'font-family: courier') )
            )
        );
    }
    echo $table;
}

?>
<?php include( './common/menu/footer.php' ); ?>