<?php
include( __DIR__ . '/autoload.php' );
include( __DIR__ . '/autoloadVendor.php' );
include( __DIR__ . '/../src/autoloader.php' );

/*
 * set up WScore.
 */
use wsCore\Core;
Core::go();
Core::setPdo( array( 'dsn' => 'sqlite:' . __DIR__ . '/task/data/tasks.sqlite' ) );
Core::get( '\task\model\tasks' );

/*
 * task demo using Symfony2's HttpKernel and Routing.
 */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;

$routes = new RouteCollection();
$routes->add( 'hello', new Route( '/list', array( '_controller' =>
    function(  Request $request ) {
        $controller = Core::get( 'task\TaskController' );
        return array( $controller, 'actIndex' );
    }
) ) );

$request = Request::createFromGlobals();

$context = new RequestContext();
$context->fromRequest( $request );

$matcher = new UrlMatcher( $routes, $context );

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber( new RouterListener( $matcher ) );

$resolver = new ControllerResolver();

$kernel = new HttpKernel( $dispatcher, $resolver );

$kernel->handle( $request )->send();