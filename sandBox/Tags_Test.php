<?php
require( './Tags.php' );

$tags = new Tags();

echo $tags()->a( 'a link' )->href( 'do.php' )->target( '_blank' );

echo $tags( 'a', 'a link' )->href( 'do.php' )->target( '_blank' );

echo $tags()->a( 'a link' )->href( 'do.php' )->target( '_blank' )->_class( 'myClass' )->_class( 'myClass2' );
echo $tags()->a( 'a link' )->href( 'do.php' )->target( '_blank' )->_class( 'myClass' )->_class( 'myClass2', FALSE );

echo $tags( 'a', 'a link' )->href( 'do.php' )->target( '_blank' )->style( 'style1' )->style( 'style2' );
echo $tags( 'a', 'a link' )->href( 'do.php' )->target( '_blank' )->style( 'style1' )->_style( 'style2', FALSE );

echo $tags()->div(
    'this is a text',
    $tags()->a( 'a link' )->href( 'do.php' )->target( '_blank' ),
    $tags()->a( 'a link' )->href( 'do1.php' )->target( '_blank' ),
    $tags()->a( 'a link' )->href( 'do2.php' )->target( '_blank' )
);

echo $tags()->div()->_class( 'divClass' )->contain_(
    'this is a text',
    $tags()->div(
        $tags()->a( 'a link' )->href( 'do.php' )->target( '_blank' ),
        $tags()->a( 'a link' )->href( 'do1.php' )->target( '_blank' )
    ),
    $tags()->a( 'a link' )->href( 'do2.php' )->target( '_blank' )
);

echo $tags()->input()->required();

$unsafe = 'unsafe" string';
echo $tags()->input()->value( $unsafe );
echo $tags()->input()->value( Tags::wrap_( $unsafe ) );
