DataRecord Class
================

this is a development memo.

Which Dao's method DataRecord uses
----------------------------------

Currently,
: getIdName()
: getModelName()
: popHtml( $html_type, $name, $value )
: propertyName( $name )

Future,

validate( $validator, $data )
: pass $validator object (DataIO) and DataRecord's _properties_ data,
  and Dao will validate the data for ONLY the accesible data.

  external API will be validate( $validator ).
  internaly validateData( $validator ) and call Dao's validate method.

popHtml( $type, $prop_name, $value )
: pass type of html (either html, form, or raw), property name, and 
  its value if present. 
: will return appropriate form or html text. for html text, 
  its html entities are encoded. 

Validation
----------

###Old style validation using pggCheck.

was something like;

    $pgg = new pggCheck();
    $err = $dao->checkInput( $pgg );

###a new way?

just an idea for using $dao directly.

    $dio = Core::get( 'DataIO' );
    $dao->restrict( array( 'user_status', ... ) );
    $dao->checkData( $dio ); // only checks for un-restricted data.
    $data  = $dio->pop();
    $isErr = $dio->popErrors( $errors );

then, from DataRecord point of view

    DataRecord::validate( $dio, $check=NULL, $restrict=array() ) {
        $this->restrict( $restrict );
        $method = ($check) ?: 'checkData';
        $dao->$method( $dio );
        $this->properties = array_merge( $this->properties, $dio->popData() );
        $dao->_is_valid_ = $dio->popErrors( $this->_errors_ );
    }

how's this?

