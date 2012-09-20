Dba Class: DataBase Access
==========================

Dba class is the center piece of DbAccess folder. 

Examples
--------

### Setting Up PDO

Accesses MySql test database using Pdo. 

Core::setPdo( 'db=mysql dbname=test user=test passwd=test' );
$dba = Core::get( '\wsCore\DbAccess\Dba' );

###Getting data

Getting data from myTable whose name contains 'Mike'.

    $data = $dba->table( 'myTable' )->find( 'name', 'Mike' );
    $data = $dba->table( 'myTable' )->where( 'name', 'Mike' )->select()->fetchAll();

Getting data whose name contains 'Mike' with id greater or equal to 10. 

    $data = $dba->table( 'myTable' )->find( [ 'name', '%Mike%', 'LIKE' ], [ 'id', 10, '>=' ],  );

use primary key to get a data. 

    $data = $dba->table( 'myTable', 'id' )->findById( 10 );

get only the first data.

    $data = $dba->table( 'myTable' )->where( 'name', 'Mike' )->first();

get only 10 data.

    $data = $dba->table( 'myTable' )->where( 'name', 'Mike' )->first(10);

offset 10, limit 10. 

    $data = $dba->table( 'myTable' )->where( 'name', 'Mike' )->first( 10, 10 );
    $data = $dba->table( 'myTable' )->where( 'name', 'Mike' )->limit(10)->offset(10)->select()->fetchAll();

###More About Where

find name contains Mike who is age >= 20

    $data = $dba->table( 'myTable' )->like( 'name', '%Mike%' )->gt( 'age', 20 )->select();

find something is NULL.

    $data = $dba->table( 'myTable', 'id' )->isNull( 'name' )->select()->fetchAll();

###Insert data

insert data into table and get the last ID.

    $id = $dba->table( 'myTable' )->values( array( 'name'=>'Alan Kay' ) )->insert()->lastId();

###Update data

    $dba->table( 'myTable' )->values( array( 'name' => 'Bob Dylan' ) )->where( 'id', 10 )->update();

###Deleting data

    $dba->table( 'myTable' )->where( 'id', 10 )->makeSQL( 'DELETE' )->exec();


Prepared Statement
------------------

As a default, wsCore uses prepared statement in the DbAccess library. 
i.e. all of the above examples uses prepared statement internally as well. 

###Run Prepared Statement

To use explicitly prepared statement, 

    $sql = 'SELECT * FROM myTable WHERE name = ?';
    $pre = array( 'Mike' );
    $dba->exec( $sql, $pre );

###Building Prepared Statement

    $data = array(
        'name'  => 'Eagles',
        'music' => 'Pops',
    );
    $dba->prepare( $data );
    $sql = "INSERT INTO myMusic ( name, music ) VALUES( {$data{'name"}}, {$data{'music"}} )";
    $dba->exec( $sql, $data );

###Using quotes

To use quoted sql instead of prepared statement,

    $dba->prepQuoteUseType = 'quote';

if you want to use quote for all the sql statement, 

    \wsCore\DbAccess\Sql::$pqDefault = 'quote'. 

To undo the quote to prepared statement, use 'prepare' instead of 'quote' in the examples above. 

