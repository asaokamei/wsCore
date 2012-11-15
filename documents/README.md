WScore Documents
================

A PHP framework for DataMapper and quick HTML form generator that is small in size and intuitive to use.

DataMapper like Doctrine 2.


How to Start
------------
Starting WScore Framework.

    Core::go();

Set up Pdo for database access in easy mode.

    Core::goEasy();
    Core::setPdo( 'db=mysql dbname=test user=test passwd=test' );
    // using Query to get data from myTable table in RDB.
    $query = Core::get( 'Query' );
    $data  = $query->table( 'myTable' )->w( 'name' )->eq( 'Mike' )->select();
    // using Entity Manager
    $em     = Core::get( 'EntityManager' );
    $entity = $em->newEntity( 'friend' );

More Contents
-------------

[DataBase Access](DbAccess)

[Validation](Validator)

HTML

Web

Dependency Injection

Aspect Oriented Programming

Logging

