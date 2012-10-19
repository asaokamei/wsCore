Relations
=========

Use DataRecord to manage relationship between entities. 

Basic Usage
-----------

There are several types of relations are available, but 
all of them have the unified interface. 

###setting relation

    // relate contact with friend. 
    $friend->relation( 'contact' )->set( $contact );

###getting related data

    $contacts = $friend->relation( 'contact' )->get();

All of the relationship has the identifying name. 

###Dao SetUp and Object Pooling

To make relations work, Dao (or model)'s relations must be 
appropriately setup. 

Relation object uses Dao's factory method, or rather, simple 
object pooling when calling each other. To do this without 
much coding, Dao's inherited objects are all pooled. 

When using relation, make sure all the concerning dao's are 
instantiated (and thus pooled) before using relation. 


Simple Relationships
--------------------

###Database example

when you have two tables, such as: 

    friend (
        friend_id   SERIAL primary key,
        friend_name text,
    );
    contact (
        contact_id   SERIAL primary key,
        friend_id    int,
        contact_info text,
    );

the two tables are related by friend_id in contact table.

###Setting Relationship

Instantiate Dao used in all the relationship before using relations. 

    // make Dao in prior. 
    $daoFriend  = Core::get( 'friend' );
    $daoContact = Core::get( 'contact' );
    
    // get friend record
    $friend = $daoFriend->getRecord();
    $friend->set( 'friend_name', 'my friend' );

    // get contact record.
    $contact = $daoContact->getRecord();
    $contact->set( 'contact_info', 'contact method' );

    // relate contact with friend. 
    $friend->relation( 'contact' )->set( $contact );
    // or, the other way around.
    $contact->relation( 'friend_id' )->set( $friend );

all the relation must have an identifier name, such as 
'friend_id' and 'contact'. 

###Getting Related Records

it is easy to retrieve the related records. 

    // related them
    $friend   = $daoFriend->find(1);
    $contacts = $friend->relation( 'contact' )->get();
    
    // or, the other way around,
    $contact = $daoContact->find(1);
    $friends = $contact->relation( 'friend_id' )->get();

the returned $friends is an array of DataRecords. 
TODO: consider creating _collection_ object to store 
DataRecords for future Cena protocol. 

###Deleting Relation

to be written

###Dao SetUp for HasOne

contact table has 'HasOne' relation with friend table.

set up $relations in Contact's dao as follows. 

    protected $relations = array(
        'friend_id' => array(
            'relation_type' => 'HasOne',
            'source_column' => null, // use target_column.
            'target_model'  => 'Dao_Friend',
            'target_column' => null, // use target id name. 
        ),
    );

The array key means:
*   relation_type: set to 'HasOne'
*   source_column: column name. uses target column if not set. 
*   target_model: name of dao (or model). 
*   target_column: column name. uses target's id name if not set. 


###Dao SetUp for HasRefs

friend table has 'HasRefs' relationship with contact table, 
which is the opposite of HasOne.

set up $relations in Friend's dao as follows.

    protected $relations = array(
        'contact' => array(
            'relation_type' => 'HasRefs',
            'source_column' => null, // use id name of source. 
            'target_model'  => 'Dao_Contact',
            'target_column' => null, // use source column. 
        ),
    );

The array key means:
*   relation_type: set to 'HasRefs'
*   source_column: column name. uses source's id name if not set. 
*   target_model: name of dao (or model). 
*   target_column: column name. uses source_column if not set. 


Many-to-Many Relationships
--------------------------

###HasJoined

For relationship using join-table. 
details to be written. 

    protected $relations = array(
        'group' => array(
            'relation_type' => 'HasJoined',
            'join_table'    => 'friend2group', // same as the relation name
            'target_model'  => 'Dao_Group',
            //'join_source_column' => null, // use id
            //'join_target_column' => null, // use id
            //'source_column' => null, // use id
            //'target_column' => null, // use id.
        ),
    );

The array key means:

the join table looks like:

    CREATE TABLE friend2group (
      group_code     varchar(64) NOT NULL,
      friend_id      int NOT NULL,
      constraint friend2group_id PRIMARY KEY (
        group_code, friend_id
      )
    )


###HasJoinDao

Many-to-many using join table with Dao (model), i.e. join table 
with a primary key. 

    protected $relations = array(
        'network' => array(
            'relation_type' => 'HasJoinDao',
            'join_model'    => 'Dao_Network',
            'join_source_column' => 'friend_id_from',
            'join_target_column' => 'friend_id_to',
            'target_model'  => 'Dao_Friend',
            //'source_column' => null, // use id.
            //'target_column' => null, // use id.
        ),
    );

With HasJoinDao, relationship can have its own values, and can be
set and retrieved as follows. 

    $friend1 = $friend->find(1);
    $friend2 = $friend->find(2);
    $friend1->relation( 'network' )->setValues( array( 'comment' => 'my good friend', 'status' => 2 ) )->set( $friend2 );
    // get relationship between friend1 and friend2. 
    $friends = $friend1->relation( 'network' )->get();
    echo $friends[0]->get( 'comment' ); // shows 'my good friend'. 

the join table looks like:

    CREATE TABLE network (
      network_id      SERIAL,
      friend_id_from  int NOT NULL,
      friend_id_to    int NOT NULL,
      comment         text,
      status          int,
      created_at      datetime,
      updated_at      datetime,
      constraint network_id PRIMARY KEY (
        network_id
      )
    )

note that the join table has primary key (network_id), 
some extra fields to be filled. 

###HasMany

to be written. 
probably for non-RDBs. 

