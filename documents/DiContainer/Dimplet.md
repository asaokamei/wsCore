Dimplet
=======

Dimplet is a Simple Container for Dependency Injection Management
that is small in size but full of features.

automated dependency injection:
: uses interface to automatically inject dependency.
id chain:
: id's are chained when obtaining a value.
getting a object is easy
: use class name as id to construct an object of the class.

Based on Pimple.

Starting Dimplet
----------------

create Dimplet object, using new.

    $container = new Dimplet();

use set and get method with id.

    $container->set( $id, $value );
    $value = $container->get( $id );

create an object from class name if $id is a className.

    $obj = $container->get( $className );

More Details
------------

###value chain

the retrieval of value follows the value chain.

    $container->set( $id,    $value );
    $container->set( $value, $value2 );
    $value = $container->get( $id );
    echo $value; // same as $value2.

###Getting an Object from Class Name

enter class name as id

    $obj = $container->get( $className );
    echo get_class( $obj ); // same as $className...

class name is chained as well.

    $container->set( $className, $className2 );
    $obj = $container->get( $className );
    echo get_class( $obj ); // same as $className2...

the object's dependencies are automatically injected
if the object implements an "injection" interface
as described in the subsequent section.

to create object exactly the way you wanted, use closure.

    $container->set( $id, function($c) {
        $obj = new AnyClass();
        $obj->service = new SomeService();
        return $obj;
    } );
    $value = $container->get( $id );
    echo get_class( $obj ); // same as "AnyClass"...
    echo get_class( $obj->service ); // same as "SomeService"...

###get and fresh methods

get method will reserve the generated value inside Dimplet, and returns
if the value if it is already created.

fresh method will just generate a value, and returns it.

    $obj  = $container->get( $className );
    $obj2 = $container->get( $className );
    $obj3 = $container->fresh( $className );
    $obj == $obj2;   // all objects have the same property
    $obj == $obj3;
    $obj === $obj2;  // but $obj3 is a different instance.
    $obj !== $obj3;

###Modifying services after creation

It is possible to modify a value after creation, just like Pimple.

    $container->extend( $className, function( $obj, $c ) {
        $obj->service = new OtherService();
    } );
    $obj = $container->get( $className );
    echo get_class( $obj->service ); // same as "OtherService"...

note: although the API reamins the same as Pimple's, the
implementation has been widely changed. So... it is more
powerful and the behavior is hopefully more intuitive...

    $container->extend( $className, function( $obj, $c ) {
        $obj->service = new SomeService();
    } );
    $container->extend( $className2, function( $obj, $c ) {
        $obj->service2 = new OtherService();
    } );
    $container->set( $className, $className2 );
    $obj = $container->get( $className );
    echo get_class( $obj ); // same as $className2...
    echo get_class( $obj->service ); // same as "SomeService"...
    echo get_class( $obj->service2 ); // same as "OtherService"...


Automated Dependency Injection Based on Interface
-------------------------------------------------

Prepare an injection interface for a service.

    namespace Goodies;
    interface injectServiceInterface {};
    class Service {}

implement the injection interface for the service to inject into your object.

    namespace my;
    class object implements \Goodies\injectServiceInterface {
        function injectService( $service ) {
            $this->service = $service;
        }
    }

that's it.

