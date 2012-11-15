WScore Documents
================

API and how to use WScore framework.

Some of them are a wish list at this point. 

Call it a Document Driven Development... 

How to Start
------------
Starting WScore Framework.

    Core::go();

Set up Pdo for database access in easy mode.

    Core::goEasy();
    Core::setPdo( 'db=mysql dbname=test user=test passwd=test' );
    $dba = Core::get( 'DbAccess' );
    $data = $dba->table( 'myTable' )->find( 'name', 'Mike' )->first();

More Contents
-------------

[DataBase Access](DbAccess)

[Validation](Validator)

HTML

Web

Dependency Injection

Aspect Oriented Programming

Logging

