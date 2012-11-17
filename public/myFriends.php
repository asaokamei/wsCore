<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/../src/autoloader.php' );
use WScore\Core;
Core::go();
Core::setPdo( array( 'dsn' => 'sqlite:' . __DIR__ . '/friends/data/friends.sqlite' ) );

/** @var $front wsModule\Alt\Web\FrontMC */
$front = Core::get( '\wsModule\Alt\Web\FrontMC' );
$front->debug = true;
$front->namespace = 'friends';
$routes = array(
    'myFriends/contact/:id/type/:type'   => array( 'controller' => 'Friend', 'action' => 'contact' ),
    'myFriends/detail/:id'   => array( 'controller' => 'Friend', 'action' => 'detail' ),
    'myFriends/:id'   => array( 'controller' => 'Friend', 'action' => 'info' ),
    'myFriends/setup' => array( 'controller' => 'Friend', 'action' => 'setup' ),
    'myFriends/'      => array( 'controller' => 'Friend', 'action' => 'index' ),
    'myFriends'       => array( 'controller' => 'Friend', 'action' => 'index' ),
);
$front->router->set( $routes );
$front->run();
