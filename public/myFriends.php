<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/../src/autoloader.php' );
use WScore\Core;

if( !$front = apc_fetch( 'myFriend.app' ) ) {

    Core::go();
    Core::setPdo( array( 'dsn' => 'mysql:dbname=test_friends', 'username' => 'admin', 'password' => 'admin' ) );

    /** @var $front wsModule\Alt\Web\FrontMC */
    /** @var $request \wsModule\Alt\Web\Request */
    /** @var $router \wsModule\Alt\Web\Router */
    $front          = Core::get( 'wsModule\Alt\Web\FrontMC' );
    $front->request = Core::get( 'wsModule\Alt\Web\Request' );
    $front->router  = Core::get( 'wsModule\Alt\Web\Router' );
    $front->setDefaultParameter( array(
        'namespace'  => 'friends',
        'controller' => 'Friend',
        'action'     => 'index'
    ) );
    $routes = array(
        'myFriends/group/:gCode'           => array( 'action' => 'groupMod' ),
        'myFriends/group'                  => array( 'action' => 'group' ),
        'myFriends/contact/:id/type/:type' => array( 'action' => 'contactNew' ),
        'myFriends/contact/:id/:cid'       => array( 'action' => 'contactMod' ),
        'myFriends/detail/:id'             => array( 'action' => 'detail' ),
        'myFriends/setup'                  => array( 'action' => 'setup' ),
        'myFriends/:id'                    => array( 'action' => 'info' ),
        'myFriends/'                       => array( 'action' => 'index' ),
        'myFriends'                        => array( 'action' => 'index' ),
    );
    $front->router->set( $routes );
    apc_store( 'myFriend.app', $front );
}

$parameter = $front->router->match( $front->request->getPathInfo( $_SERVER ) );

$front->run( $parameter );
