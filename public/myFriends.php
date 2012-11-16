<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/../src/autoloader.php' );
use WScore\Core;
Core::go();
Core::setPdo( array( 'dsn' => 'sqlite:' . __DIR__ . '/friends/data/friends.sqlite' ) );

/** @var $front wsModule\Alt\Web\FrontMC */
Core::get( '\friends\model\Friends' );
$front = Core::get( '\wsModule\Alt\Web\FrontMC' );
$front->debug = true;
$front->namespace = 'friends';
$routes = array(
    'myFriends/setup'        => array( 'controller' => 'Friend', 'action' => 'setup' ),
    'myFriends/' => array( 'controller' => 'Friend',  'action' => 'index' ),
    'myFriends'  => array( 'controller' => 'Friend',  'action' => 'index' ),
);
$front->router->set( $routes );
$front->run();
