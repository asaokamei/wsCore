<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/../src/autoloader.php' );
use WScore\Core;

Core::cache();
if( !$front = Core::fetch( 'myTask.app' ) ) {

    $container = Core::go();
    $container->set( 'Pdo', array( 'dsn' => 'sqlite:' . __DIR__ . '/task/data/tasks.sqlite' ) );

    /** @var $front wsModule\Alt\Web\FrontMC */
    /** @var $request \wsModule\Alt\Web\Request */
    /** @var $router \wsModule\Alt\Web\Router */
    $front          = $container->get( 'wsModule\Alt\Web\FrontMC' );
    $front->request = $container->get( 'wsModule\Alt\Web\Request' );
    $front->router  = $container->get( 'wsModule\Alt\Web\Router' );
    $front->setDefaultParameter( array(
        'namespace'  => 'task',
        'controller' => 'task',
        'action'     => 'index'
    ) );

    $routes           = array(
        'myTasks/printO/:name' => array( 'controller' => 'task', 'action' => 'PrintO' ),
        'myTasks/printO'       => array( 'controller' => 'task', 'action' => 'PrintO', 'name' => 'em' ),
        'myTasks/setup'        => array( 'controller' => 'task', 'action' => 'setup' ),
        'myTasks/new'          => array( 'controller' => 'task', 'action' => 'new' ),
        'myTasks/done/:id'     => array( 'controller' => 'task', 'action' => 'done' ),
        'myTasks/task/:id'     => array( 'controller' => 'task', 'action' => 'task' ),
        'myTasks/:action/:act' => array( 'controller' => 'task', 'act' => '' ),
        'myTasks/'             => array( 'controller' => 'task', 'action' => 'index' ),
        'myTasks'              => array( 'controller' => 'task', 'action' => 'index' ),
    );
    $front->router->set( $routes );

    Core::store( 'myTask.app', $front );
}

$parameter = $front->router->match( $front->request->getPathInfo( $_SERVER ) );
$front->run( $parameter );
