<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/../src/autoloader.php' );
use WScore\Core;
Core::go();
Core::setPdo( array( 'dsn' => 'mysql:dbname=test_friends', 'username' => 'admin', 'password' => 'admin' ) );

/** @var $front wsModule\Alt\Web\FrontMC */
/** @var $request \wsModule\Alt\Web\Request */
/** @var $router \wsModule\Alt\Web\Router */
$front          = Core::get( 'wsModule\Alt\Web\FrontMC' );
$request        = Core::get( 'wsModule\Alt\Web\Request' );
$router         = Core::get( 'wsModule\Alt\Web\Router' );
$front->request = $request;
$front->setDefaultParameter( array(
    'namespace'  => 'friends',
    'controller' => 'CenaFriend',
    'action'     => 'index'
) );
$routes = array(
    'cenaFriends/group/:gCode'           => array( 'action' => 'groupMod' ),
    'cenaFriends/group'                  => array( 'action' => 'group' ),
    'cenaFriends/contact/:id/type/:type' => array( 'action' => 'contactNew' ),
    'cenaFriends/contact/:id/:cid'       => array( 'action' => 'contactMod' ),
    'cenaFriends/detail/:id'             => array( 'action' => 'detail' ),
    'cenaFriends/setup'                  => array( 'action' => 'setup' ),
    'cenaFriends/:id'                    => array( 'action' => 'info' ),
    'cenaFriends/'                       => array( 'action' => 'index' ),
    'cenaFriends'                        => array( 'action' => 'index' ),
);
$router->set( $routes );
$parameter = $router->match( $request->getPathInfo() );

$front->run( $parameter );
