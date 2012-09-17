Dba Class: DataBase Access
==========================

Dba class is the center piece of DbAccess folder. 

#Examples

## Setting Up PDO

Accesses MySql test database using Pdo. 

Core::setPdo( 'db=mysql dbname=test user=test passwd=test' );
$dba = Core::get( '\wsCore\DbAccess\Dba' );

##Getting data

Getting data from myTable whose name contains 'Mike'.

    $data = $dba->table( 'myTable' )->find( 'name', '%Mike%', 'LIKE' );
    $data = $dba->table( 'myTable' )->find( [ 'name', '%Mike%', 'LIKE' ], [ 'id', 10, '>=' ],  );
    $data = $dba->table( 'myTable' )->where( 'name', '%Mike%', 'LIKE' )->select()->fetchAll();

use primary key to get a data. 

    $data = $dba->table( 'myTable', 'id' )->findById( 10 );

find something is NULL. 

    $data = $dba->table( 'myTable', 'id' )->find( 'name', Core::F('NULL') );
    $data = $dba->table( 'myTable', 'id' )->whereNull( 'name' )->select()->fetchAll();

##Insert data

insert data into table and get the last ID.

    $id = $dba->table( 'myTable' )->values( array( 'name'=>'Alan Kay' ) )->insert()->lastId();

##Update data

    $dba->table( 'myTable' )->values( array( 'name' => 'Bob Dylan' ) )->where( 'id', 10 )->update();

##Deleting data

    $dba->table( 'myTable' )->where( 'id', 10 )->makeSQL( 'DELETE' )->exec();


#Prepared Statement

All of the above examples uses prepared statement automatically. 

##Run Prepared Statement

To use explicitly prepared statement, 

    $sql = 'SELECT * FROM myTable WHERE name = ?';
    $pre = array( 'Mike' );
    $dba->exec( $sql, $pre );

##Building Prepared Statement

    $data = array(
        'name' => 'Eagles',
        'music' => 'Pops',
    );
    $dba->prepare( $data );
    $sql = "INSERT INTO myMusic ( name, music ) VALUES( {$data{'name"}}, {$data{'music"}} )";
    $dba->exec( $sql, $data );

##Using quotes

To use quoted sql instead of prepared statement,

    $dba->prepQuoteUseType = 'quote';

if you want to use quote for all the sql statement, 

    \wsCore\DbAccess\Sql::$pqDefault = 'quote'. 

To undo the quote to prepared statement, use 'prepare' instead of 'quote' in the examples above. 

