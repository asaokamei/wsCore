DbAccess
========

This service provides easy database access.

###Setting Pdo object

use Core's setPdo to set up Pdo object for project.

    // use simple text to set up Pdo.
    Core::setPdo( 'db=mysql dbname=test user=test passwd=test' );
    // use complex array and dsn string to set up Pdo.
    Core::setPdo(
        array( 'dsn' => 'mysql: dbname=test host=localhost',
        'username' => 'test',
        'password' => 'pass word',
    ) );

An id of 'Pdo' is created in the Core repository.

You can get the Pdo object with the configuration as;

    $pdo = Core::get( 'Pdo' );

Alternatively, you can create different configuration of Pdo
with different id.

    Core::setPdo( 'db=mysql dbname=test2 user=test passwd=test', 'Pdo2' );
    $pdo2 = Core::get( 'Pdo2' );

The id for the Pdo can be used for automated dependency injection
as indicated in the constructor annotation.

Query Class
-----------

DataBase Access class; a core object for the DbAccess family.

ORM
---

to be written, to be implemented.

###Dao

Database Access Object.

###DataRecord

a data object wrapping dao.

###ActiveRecord

Active Record design pattern implementation added to DataRecord object.


Other Classes
-------------

###PdObject

A thin layer of Pdo wrapper. 

its responsibilities are:
*   easily executing prepared statement, 
*   use bindValue if parameter type is present, 
*   manage fetch mode, 

and some more. 

And for future, PdObject will have PdoLogger class wrapped in 
development mode. 

###SqlObject

A small class to keep SQL statement information, such as table, where 
clause, etc. as its properties. 

SqlObject is constructed inside Query object, and passed to SqlBuilder 
to build SQL statements. 

###SqlBuilder

A static class to build SQL statement from SqlObject. 

    $sqlStatement = SqlBuilder::makeSelect( $sqlObj );

accepts array as well.

    $sql = array(
        'table' => 'myTable',
        'columns' => [ 'data_id', 'name' ],
        'distinct' => TRUE
    );
    $sqlStatement = SqlBuilder::makeSelect( $sql );

###Rdb

Used to generate Pdo object inside Core::setPdo.

