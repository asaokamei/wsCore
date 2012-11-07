<?php
require_once( __DIR__ . '/../src/autoloader.php' );
use wsCore\Core;
use wsCore\Utilities\Tools;
use wsCore\Html\Form;

Core::go();

// get inputs and passwords if generate button is clicked.
if( $_REQUEST[ 'generate' ] ) {
    $input     = get_inputs();
    $passwords = generate_password( $input );
}
else {
    $input = array(
        'length' => '12',
        'symbol' => '',
        'count'  => '10',
    );
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
    $dio->pushValue( 'length' );
    $dio->pushValue( 'symbol' );
    $dio->pushValue( 'count' );

    $input = $dio->pop();
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
    <?php var_dump( $passwords ); ?>
    <footer class="footer">
        <hr>
        <p>WScore Developed by WorkSpot.JP<br />
            thanks, bootstrap. </p>
    </footer>
</div>
</body>
</html>