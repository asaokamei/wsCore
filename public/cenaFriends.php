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
    'cenaFriends/group/:gCode'   => array( 'controller' => 'CenaFriend', 'action' => 'groupMod' ),
    'cenaFriends/group'   => array( 'controller' => 'CenaFriend', 'action' => 'group' ),
    'cenaFriends/contact/:id/type/:type'   => array( 'controller' => 'CenaFriend', 'action' => 'contactNew' ),
    'cenaFriends/contact/:id/:cid'   => array( 'controller' => 'CenaFriend', 'action' => 'contactMod' ),
    'cenaFriends/detail/:id'   => array( 'controller' => 'CenaFriend', 'action' => 'detail' ),
    'cenaFriends/setup' => array( 'controller' => 'CenaFriend', 'action' => 'setup' ),
    'cenaFriends/:id'   => array( 'controller' => 'CenaFriend', 'action' => 'info' ),
    'cenaFriends/'      => array( 'controller' => 'CenaFriend', 'action' => 'index' ),
    'cenaFriends'       => array( 'controller' => 'CenaFriend', 'action' => 'index' ),
);
$front->router->set( $routes );
$front->run();
