<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/../src/autoloader.php' );
use wsCore\Core;
Core::go();
Core::setPdo( array( 'dsn' => 'sqlite:' . __DIR__ . '/task/data/tasks.sqlite' ) );

/** @var $front wsModule\Alt\Web\FrontMC */
Core::get( '\task\model\tasks' );
$front = Core::get( '\wsModule\Alt\Web\FrontMC' );
$front->debug = true;
$front->namespace = 'task';
$routes = array(
    'myTasks/new'      => array( 'controller' => 'task', 'action' => 'new' ),
    'myTasks/task/:id'      => array( 'controller' => 'task', 'action' => 'task' ),
    'myTasks/:action/:act' => array( 'controller' => 'task', 'act' => '' ),
    'myTasks' => array( 'controller' => 'task', 'action' => 'index' ),
);
$front->router->set( $routes );
$front->run();
