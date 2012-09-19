<?php
require( __DIR__ . '/../../wsCore/Html/Tags.php' );
require( __DIR__ . '/../../wsCore/Html/Element.php' );
use wsCore\Html\Element as Element;

$element = new Element();

echo $element()->input( 'text', 'user_name', 'taro-san', array( 'class' => 'myClass' ) );
echo $element()->input( 'date', 'user_bdate', '1989-01-01' );
echo $element()->textArea( 'user_memo', 'memo memo meeemo' )->contain_( 'moremoremore');
echo $element()->radio( 'user_OK', 'YES' );
echo $element()->check( 'user_OK', 'YES' );

echo $element()->radioLabel( 'user_OK', 'YES', 'are you OK?' );
echo $element()->checkLabel( 'user_OK', 'YES', 'are you OK?' );


$ages = array(
    array( '10', 'teenage' ),
    array( '20', 'twenties' ),
    array( '30', 'thirtish' ),
);

echo "\n----\n";
echo $element->radioBox( 'user_age', $ages, '20' );
echo "\n----\n";
echo $element->checkBox( 'user_age', $ages, '30' );
echo "\n----\n";
