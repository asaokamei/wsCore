<?php
namespace wsTests\Html;
require( __DIR__ . '/../../autoloader.php' );
use wsCore\Html\Form as Form;

$form = new Form();

/** @var $form Form */
echo $form()->input( 'text', 'user_name', 'taro-san', array( 'class' => 'myClass', 'ime' => 'ON' ) );
echo $form()->input( 'date', 'user_bdate', '1989-01-01' )->_ime( 'OFF' );
echo $form()->textArea( 'user_memo', 'memo memo meMeMeMo' )->contain_( $form()->input( 'time', 'user_time' ) );
echo $form()->radio( 'user_OK', 'YES' );
echo $form()->check( 'user_OK', 'YES' );

echo $form()->radioLabel( 'user_OK', 'YES', 'are you OK?' );
echo $form()->checkLabel( 'user_OK', 'YES', 'are you OK?' );


$ages = array(
    array( '10', 'teenage' ),
    array( '20', 'twenties' ),
    array( '30', 'thirtish' ),
);

echo "\n----\n";
echo $form->radioBox( 'user_age', $ages, '20' );
echo "\n----\n";
echo $form->checkBox( 'user_age', $ages, '30' );
echo "\n----\n";

$lang = array(
    array( 'eng', 'english' ),
    array( 'ger', 'german', 'europe' ),
    array( 'fra', 'french', 'europe' ),
    array( 'spa', 'spanish', 'europe' ),
    array( 'jpn', 'japanese' ),
    array( 'zhi', 'chinese', 'asia' ),
    array( 'kor', 'korean', 'asia' ),
);
echo $form()->select( 'user_lang', $lang, 'ger', array( 'multiple' => '' ) );

$list = array( 'item1', 'more', 'another' );
echo $form()->listBox( $list );
