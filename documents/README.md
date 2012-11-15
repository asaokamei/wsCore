WScore Documents
================

A PHP framework for DataMapper and quick HTML form generator
that is small in size and intuitive to use.

DataMapper like Doctrine 2.
Combined with validation rules and html form generator in configuration
made it easy to develop and maintain application.

How to Start
------------
Starting WScore Framework.

    Core::go();

Set up Pdo for database access in easy mode.

    Core::goEasy();
    Core::setPdo( 'db=mysql dbname=test user=test passwd=test' );

then, use Query object to manipulate table in the RDB.

    // using Query to get data from friend table in RDB.
    $query = Core::get( 'Query' );
    $data  = $query->table( 'friend' )->w( 'name' )->eq( 'Mike' )->select();
    $data[ 'name' ] = 'Bob';
    $query->table( 'friend' )->insert( $data );

or, use EntityManager to get entity object for a friend.

    // using Entity Manager
    $em     = Core::get( 'EntityManager' );
    $entity = $em->getEntity( 'friend', 10 );
    $entity->name = 'my friend';
    $em->save();

More Contents
-------------

[DataBase Access](DbAccess)

[Validation](Validator)

HTML

Web

Dependency Injection

Aspect Oriented Programming

Logging

