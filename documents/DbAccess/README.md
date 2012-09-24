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

Dba, Object for DataBase Access
-------------------------------

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

###SqlBuilder

static class to build SQL statement from Sql object. 

    $sqlStatement = SqlBuilder::makeSelect( $sql );

accepts array as well.

    $sql = array(
        'table' => 'myTable',
        'columns' => [ 'data_id', 'name' ],
        'distinct' => TRUE
    );
    $sqlStatement = SqlBuilder::makeSelect( $sql );

###Sql

a class to create SQL statement dynamically. 
uses SqlBuilder to create SQL statement.

This class goes intertingle with Dba object.

###Rdb

Used to generate Pdo object inside Core::setPdo.

