<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/../src/autoloader.php' );
use WScore\Core;
Core::go();
Core::setPdo( array( 'dsn' => 'mysql:dbname=test_friends', 'username' => 'admin', 'password' => 'admin' ) );

/** @var $front wsModule\Alt\Web\FrontMC */
$front = Core::get( 'wsModule\Alt\Web\FrontMC' );
$front->debug = true;
$front->namespace = 'friends';
$routes = array(
    'myFriends/contact/:id/type/:type'   => array( 'controller' => 'Friend', 'action' => 'contactNew' ),
    'myFriends/contact/:id/:cid'   => array( 'controller' => 'Friend', 'action' => 'contactMod' ),
    'myFriends/detail/:id'   => array( 'controller' => 'Friend', 'action' => 'detail' ),
    'myFriends/setup' => array( 'controller' => 'Friend', 'action' => 'setup' ),
    'myFriends/:id'   => array( 'controller' => 'Friend', 'action' => 'info' ),
    'myFriends/'      => array( 'controller' => 'Friend', 'action' => 'index' ),
    'myFriends'       => array( 'controller' => 'Friend', 'action' => 'index' ),
);
$front->router->set( $routes );
$front->run();
