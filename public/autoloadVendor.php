<?php
require_once __DIR__ . '/../vendor/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->register();

$loader->registerNamespace( 'Symfony\\Component\\HttpFoundation', __DIR__.'/../vendor/symfony/http-foundation');
$loader->registerNamespace( 'Symfony\\Component\\Routing', __DIR__.'/../vendor/symfony/routing' );
$loader->registerNamespace( 'Symfony\\Component\\HttpKernel', __DIR__.'/../vendor/symfony/http-kernel' );
$loader->registerNamespace( 'Symfony\\Component\\EventDispatcher', __DIR__.'/../vendor/symfony/event-dispatcher' );
