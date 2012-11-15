Query Class
===========

Query class is the center piece of accessing database, such as 
MySql, PostgreSQL, and sqlite. It manages from SQL statement building, 
issuing SQL to Pdo with prepared value, and fetching data. 

QUESTION: should Query clear itself when table is set?

Examples
--------

### Setting Up PDO

Accesses MySql test database using Pdo. 

    Core::setPdo( 'db=mysql dbname=test user=test passwd=test' );
    $query = Core::get( '\WScore\DbAccess\Query' );
    $query->table( 'table' )->select();

Issue another SQL statement. 

    $query->clear(); // clears previous SQL statement information. 
    $query->table( 'myTable' )->select();

###Getting data

Getting 'name' from myTable whose name contains 'Mike'.

    $data = $query->table( 'myTable' )->select( 'name' );
    $data = $query->table( 'myTable' )->where( 'name', 'Mike' )->select( 'name' );

Getting data whose name contains 'Mike' with id greater or equal to 10. 

    $data = $query->table( 'myTable' )->w( 'name' )->contain( 'Mike' )->w( 'id' )->ge( 10 )->select();

or you can do in two or more lines. 

    $query->table( 'myTable' )->w( 'name' )->contain( 'Mike' );
    $data = $query->w( 'id' )->ge( 10 )->select();

use primary key to get a data. ※not implemented, yet.

    $data = $query->table( 'myTable', 'id' )->findById( 10 );

get only the first data. ※not implemented, yet.

    $data = $query->table( 'myTable' )->w( 'name' )->contain( 'Mike' )->first();

get only 10 data. i.e. limit to 10. 

    $data = $query->table( 'myTable' )->w( 'name' )->contain( 'Mike' )->limit(10);

offset 10, limit 10. 

    $data = $query->table( 'myTable' )->w( 'name' )->contain( 'Mike' )->limit(10)->offset(10)->select();

###More About Where

find data whose age is greater than 20, etc.

    $data = $query->table( 'myTable' )->w( 'age' )->eq( 20 )->select(); // equals to 20
    $data = $query->table( 'myTable' )->w( 'age' )->ne( 20 )->select(); // not equals to 20
    $data = $query->table( 'myTable' )->w( 'age' )->gt( 20 )->select(); // greater than 20
    $data = $query->table( 'myTable' )->w( 'age' )->ge( 20 )->select(); // greater or equals to 20
    $data = $query->table( 'myTable' )->w( 'age' )->lt( 20 )->select(); // less than 20
    $data = $query->table( 'myTable' )->w( 'age' )->le( 20 )->select(); // less or equals to 20
    $data = $query->table( 'myTable' )->w( 'age' )->between( array( 20, 30 ) )->select(); // between 20 and 30
    $data = $query->table( 'myTable' )->w( 'age' )->in( array( 20, 30, 40 ) )->select(); // either 20, 30, or 40
    $data = $query->table( 'myTable' )->w( 'age' )->notIn( array( 20, 30, 40 ) )->select(); // not 20, 30, and 40

find something is NULL.

    $data = $query->table( 'myTable' )->w( 'name' )->isNull()->select();

find something is NOT NULL.

    $data = $query->table( 'myTable' )->w( 'name' )->notNull()->select();

complex where clause using OR and parentheses.

    $data = $query->table( 'myTable' )
        ->w( 'age' )->ge( 20 )->where( '(' )
            ->w( 'name' )->startWith( 'John' )->or_()->w( 'name' )->endWith( 'Lennon' )
        ->where( ')' )->select();

###Insert data

insert data into table and get the last ID.

    $id = $query->table( 'myTable' )->insert( array( 'name'=>'Alan Kay' ) )->lastId();

###Update data

    $query->table( 'myTable' )->w( 'id' )->eq( 10 )->update( array( 'name' => 'Bob Dylan' ) );

###Deleting data

    $query->table( 'myTable' )->w( 'id' )->eq( 10 )->delete();


Prepared Statement
------------------

As a default, WScore uses prepared statement in the DbAccess library.
i.e. all of the above examples uses prepared statement internally as well. 

###Run Prepared Statement

To use explicitly prepared statement, 

    $sql = 'SELECT * FROM myTable WHERE name = ?';
    $pre = array( 'Mike' );
    $query->exec( $sql, $pre );

###Building Prepared Statement

    $data = array(
        'name'  => 'Eagles',
        'music' => 'Pops',
    );
    $query->prepare( $data );
    $sql = "INSERT INTO myMusic ( name, music ) VALUES( {$data{'name"}}, {$data{'music"}} )";
    $query->exec( $sql, $data );

complex where clause using OR and parentheses.

    $input = array( 'age' => 20, 'name1' => 'John%', 'name2' => '%Lennon' );
    $query->prepare( $input );
    $where = "age>={$input{'age'}} AND ( name LIKE {$input{'name1'}} OR name LIKE {$input{'name2'}} )";
    $data = $query->table( 'myTable' )->setWhere( $where )->select();

###Using quotes

To use quoted sql instead of prepared statement,

    $query->prepQuoteUseType = 'quote';

if you want to use quote for all the sql statement, 

    \WScore\DbAccess\Sql::$pqDefault = 'quote'.

To undo the quote to prepared statement, use 'prepare' instead of 'quote' in the examples above. 

