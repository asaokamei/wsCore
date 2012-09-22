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

###Setting Closure for Construction

set closure for an id just like Pimple.

    $container->set( 'id', function( $c ) {
        return new \some\Class(
            new \a\service(),
            new \b\service()
        );
    } );
    $obj = $container->get( 'id' ); // get \some\Class object.

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

If id's are chained, all the extension for the all the chained 
id's are applied to the object. 

    $container->extend( 'classA', function( $obj, $c ) {
        $obj->service = new SomeService();
    } );
    $container->extend( 'classB', function( $obj, $c ) {
        $obj->service2 = new OtherService();
    } );
    $container->set( 'classA', 'classB' );
    $obj = $container->get( $className );
    echo get_class( $obj );           // same as 'classB'...
    echo get_class( $obj->service );  // same as "SomeService"...
    echo get_class( $obj->service2 ); // same as "OtherService"...


Automated Dependency Injection Based on Interface
-------------------------------------------------

Constructor type automated dependency injection is available 
using PHP Annotation. 

    class Sample {
        /**
         * @DimInjection New Service
         */
        public function __construct( $service ) {
            $this->service = $service;
        }
    }
    $obj = $container->get( 'Sample' ); // 
    $obj->service;

### DimInjection Format

The basic format of @DimInjection annotation is

    @DimInjection [ [Fresh|Get] | [Obj|Raw] | None ] $id
    
    $id  : an id of a named service or a class name. 
    Fresh: gets an object as fresh. (default)
    Get  : gets reusable object. 
    Obj  : gets object. (default)
    Raw  : gets a closure to get object. 
    None : gets NULL. 

### Multiple Parameters

list all the parameters to the constructor. 

    class Sample {
        /**
         * @DimInjection New Service
         * @DimInjection Raw Service2
         */
        public function __construct( $service, $serviceGenerator ) {
            $this->service = $service;
            $this->service2 = $serviceGenerator;
        }
    }

### Injecting Null

If one of the parameter is not used, set DimInjection to None. 
In the case below, the $service2 is set to NULL. 

    class Sample {
        /**
         * @DimInjection New Service
         * @DimInjection None Service2
         */
        public function __construct( $service, $service2 ) {
            $this->service = $service;
            $this->service2 = $service2;
        }
    }
