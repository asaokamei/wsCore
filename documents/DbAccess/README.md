DbAccess
========

Classes
-------

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

###Dba

DataBase Access class; a core object for the DbAccess family. 

###Rdb

setup Pdo. 
will be refactored soon. 

###Dao

Database Access Object. 

###DataRecord

a data object wrapping dao. 

###ActiveRecord

Active Record design pattern implementation. 
