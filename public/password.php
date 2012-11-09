<?php
require_once( __DIR__ . '/../src/autoloader.php' );
use wsCore\Core;
use wsCore\Utilities\Tools;
use wsCore\Html\Form;

Core::go();

// get inputs and passwords if generate button is clicked.
$input = array(
    'length' => '12',
    'symbol' => '',
    'count'  => '5',
);
if( isset( $_REQUEST[ 'generate' ] ) ) {
    $input     = array_merge( $input, get_inputs() );
    $passwords = generate_password( $input );
}
else {
}
// build forms.
$form = new Form();
$item_count = array(
    array(  '5', ' 5 passwords' ),
    array( '10', '10 passwords' ),
    array( '15', '15 passwords' ),
);
if( !$input[ 'symbol' ] ) $input[ 'symbol' ] = false;

// get inputs from post.
function get_inputs()
{
    /** @var $dio wsCore\Validator\DataIO */
    $dio = Core::get( 'DataIO' );
    $dio->source( $_POST );
    $dio->push( 'length', 'number' );
    $dio->push( 'symbol', 'text' );
    $dio->push( 'count', 'number' );

    $input = $dio->popSafe();
    return $input;
}

// generate passwords based on inputs.
function generate_password( $input )
{
    $count = ( $input[ 'count' ] ) ?: 5;
    $passwords = array();
    for( $i = 0; $i < $count; $i ++ ) {
        $passwords[] = Tools::password( $input[ 'length' ], $input[ 'symbol' ] );
    }
    return $passwords;
}

?>
<?php include( './common/menu/header.php' ); ?>
    <h1>generate password</h1>
    <p>specify length of password, check to use symbols (!@#$ etc.), <br />and click generate password button. </p>
    <form name="password" method="post" action="password.php">
        <dl>
            <dt>length of password</dt>
            <dd><?php echo $form->input( 'text', 'length', $input[ 'length' ] ) ?></dd>
            <dt>use symbols</dt>
            <dd><label><?php echo $form->input( 'checkbox', 'symbol', 'checked' )->checked( $input[ 'symbol' ] ); ?> check if you want password to have some symbols. </label></dd>
            <dt>get # of passwords</dt>
            <dd><?php echo $form->select( 'count', $item_count, array( $input['count']) ); ?></dd>
        </dl>
        <input type="submit" name="generate" class="btn btn-primary" value="generate password">
    </form>
    <?php if( isset( $passwords ) ) var_dump( $passwords ); ?>
<?php include( './common/menu/footer.php' ); ?>