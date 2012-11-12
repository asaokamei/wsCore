<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/../src/autoloader.php' );
use wsCore\Core;
Core::go();
Core::setPdo( array( 'dsn' => 'sqlite:' . __DIR__ . '/task/data/tasks.sqlite' ) );

/** @var $front wsCore\Web\FrontMC */
Core::get( '\task\model\tasks' );
$front = Core::get( '\wsCore\Web\FrontMC' );
$front->debug = true;
$front->namespace = 'task';
$routes = array(
    '/:action/:act' => array( 'controller' => 'task', 'act' => '' ),
    '/:action'      => array( 'controller' => 'task' ),
    '' => array( 'controller' => 'task', 'action' => 'index' ),
);
$front->router->set( $routes );

include( './common/menu/header.php' );

$front->run();

include( './common/menu/footer.php' );
