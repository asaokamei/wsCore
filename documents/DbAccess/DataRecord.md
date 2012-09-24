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


