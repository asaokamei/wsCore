wsCore
======

Core is a static class
manages configurations and objects for wsCore framework. 
uses DiContainer underneath. 

#How to Start

Starting wsCore Framework. 

    Core::go();

Using easy mode; uses short name instead of full class name with name space to get objects. 

    Core::goEasy();

Using development mode; Sql and Validators are logged, and Debug object is activated. 

    Core::goDev();

Set up database access and get some data.

    Core::goEasy();
    Core::setPdo( Rdb::setup( 'db=mysql dbname=test user=test passwd=test' ) );
    $dba  = Core::get( 'DbAccess' );
    $data = $dba->table( 'myTest' )->where( 'id', 10 )->select();

#Core's functionality

##Basic Object Management

Setting your object.

    Core::set( 'id', '\Your\Path\To\ClassName' );

Getting your object (the object is reused). 

    Core::get( 'id' );

getting a new object (created just for you)

    Core::fresh( 'id' );

##DiContainer

Core class is a wrapper of Dimplet, a DiContainer based on Pimple. 
There a lot of command that are similar to Pimple. 

extend object to create

    Core::extend( 'id', '\Your\Path\To\ClassName' );

storing closure into the container. 

    Core::protect( 'id', '\Your\Path\To\ClassName' );

making the object singleton. 

    Core::share( 'id', '\Your\Path\To\ClassName' );

##Automated Dependency Injection

Please refer to Dimplet. 

