<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/../src/autoloader.php' );
use WScore\Core;

$container = Core::go();
if( !$front = Core::fetch( 'cenaFriend.app' ) ) {

    $container->set( 'Pdo', array( 'dsn' => 'mysql:dbname=test_friends', 'username' => 'admin', 'password' => 'admin' ) );

    /** @var $front wsModule\Alt\Web\FrontMC */
    /** @var $request \wsModule\Alt\Web\Request */
    /** @var $router \wsModule\Alt\Web\Router */
    $front          = $container->get( 'wsModule\Alt\Web\FrontMC' );
    $front->request = $container->get( 'wsModule\Alt\Web\Request' );
    $front->router  = $container->get( 'wsModule\Alt\Web\Router' );
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
    $front->router->set( $routes );
    $container->get( '\friends\CenaFriendController' );
    Core::store( 'cenaFriend.app', $front );
}

$parameter = $front->router->match( $front->request->getPathInfo( $_SERVER ) );
$front->run( $parameter );
