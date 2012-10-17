Relations
=========

Use DataRecord to manage relationship between entities. 

Basic Usage
-----------

There are several types of relations are available, but 
all of them have the unified interface. 

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

The details of the relationship must be defined in 
each of dao (i.e. model). 

Many-to-many relationship using join table is also supported, 
and explained in a subsequent section. 

###Deleting Relation

to be written

###Dao Instances

Relation object uses Dao's factory method, or rather, simple 
object pooling when calling each other. To do this without 
much coding, Dao's inherited objects are all pooled. 

When using relation, make sure all the concerning dao's are 
instantiated (and thus pooled) before using relation. 


Setting Up Relation in Dao
--------------------------

There are 3 types of relation available as of now. 

###HasOne

contact table has 'HasOne' relation with friend table.

set up $relations in Contact's dao as follows. 

    protected $relations = array(
            'friend_id' => array(
            'relation_type' => 'HasOne',
            'source_column' => null, // same as the relation name
            'target_model'  => 'Dao_Friend',
            'target_column' => null, // use id.
        ),
    );

###HasRefs

friend table has 'HasRefs' relationship with contact table, 
which is the opposite of HasOne.

set up $relations in Friend's dao as follows.

    protected $relations = array(
        'contact' => array(
            'relation_type' => 'HasRefs',
            'source_column' => null, // use id.
            'target_model'  => 'Dao_Contact',
            'target_column' => null, // use id name of source.
        ),
    );

###IsJoined

For relationship using join-table. 
details to be written. 

###HasMany

to be written. 
probably for non-RDBs. 

