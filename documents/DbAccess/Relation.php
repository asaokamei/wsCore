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

code example. 

    // get friend record
    $daoFriend = Core::get( 'friend' );
    $friend = $daoFriend->getRecord();
    $friend->set( 'friend_name', 'my friend' );

    // get contact record.
    $daoContact = Core::get( 'contact' );
    $contact = $daoContact->getRecord();
    $contact->set( 'contact_info', 'contact method' );

    // related them
    $friend->relation( 'contact' )->set( $contact );
    // or, the other way around,
    $contact->relation( 'friend_id' )->set( $friend );

all the relation must have an identifier name, such as 
'friend_id' and 'contact'. 

###Getting Related Records

    // related them
    $friend   = $daoFriend->find(1);
    $contacts = $friend->relation( 'contact' )->get();
    // or, the other way around,
    $contact = $daoContact->find(1);
    $friends = $contact->relation( 'friend_id' )->get();


The details of the relationship must be defined in 
each of dao (i.e. model). 

Many-to-many relationship using join table is also supported, 
and explained in a subsequent section. 

Setting Up Dao
--------------

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
