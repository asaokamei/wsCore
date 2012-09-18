DataIO, for Data Input and Output
=================================

Simple Start
------------

get DataIO object and get values from $_POST, a default data source.

    Core::goEasy();
    $dio = Core::fresh( 'dio' );
    $dio->pushValue( 'mail' );
    $dio->pushValue( 'name' );
    $data = $dio->pop();

get values using preset types from $_REQUEST.

    $dio->source( $_REQUEST ); // default is $_POST.
    $dio->push( 'mail', 'user_mail', 'required' ); // required value
    $dio->push( 'text', 'user_name', 'required' ); // required value
    $data = $dio->pop();

check errors and get error messages.
the required filter has default error message: 'required field',
which you can change.

    $dio->source( array() ); // set empty data.
    $dio->push( 'text', 'user_name', 'required' ); // will fail with required
    if( $isErr = $dio->popErrors( $errors ) ) {
        foreach( $errors as $name => $err_msg ) {
            echo "Error in $name: $err_msg\n"; // Error in user_name: required field
        }
    }

setting error messages.

    $dio->push( 'number', 'number', 'min:10|max:20|err_msg:enter number between 10 and 20' );

filter is either a text, or an array. they represent the same filters.

    $dio->push( 'age', 'number', 'min:10|max:20|err_msg:enter number between 10 and 20' );
    $dio->push( 'age', 'number', array( 'min' => 10, 'max' => 20, 'err_msg' => 'enter number between 10 and 20' ) );

API
---

###pushValue

pushValue checks for a named value in the source.

$name
: data is taken from $source[ $name ].
$filters
: is either an array or text representation of filtering rules.
$value
: returns the found value. FALSE if error, NULL if not present in source data.

    $dio->pushValue( $name, $filters='', &$value=NULL );

###push

pushValue checks for a named value in the source with predefined filter type.

    $dio->push( $name, $type, $filters='', &$value=NULL );


Available Filters
-----------------

Most of the filters are defined in Validator;
only multiple and sameWith filters are in the DataIO.

###required

the value is required.

    $dio->pushValue( 'name', 'required' );
    $dio->pushValue( 'name', array( 'required'=>TRUE ) );

when required data is missing, error will be recorded with
a predefined error message. To change the error message;

    $dio->validator->filterOptions[ 'required' ][ 'err_msg' ] = 'required data';

###multiple (in DataIO)

searches for multiple names and combines into one data.
it is recommended to use array-style filter representation
for multiple filter because it has complex options

    $dio->pushValue( 'date', array(
        'multiple' => array( 'prefix' => 'year,month,day', 'connector' => '/' ) )
    );
best is to use pre-defined multiple filters

    $dio->push( 'user_bdate', 'date' );
    $dio->push( 'user_tel', 'tel' );
    $dio->push( 'user_fax', 'tel' );

###sameWith (in DataIO)


###pattern



Predefined Types
----------------

