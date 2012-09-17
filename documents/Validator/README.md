Validator
=========

Classes
-------

###Validator

Validates a single value, or an array of value with same set of validation rule. 

examples:

    Core::goEasy();
    $validator = Core::fresh( 'Validator' );
    $value = 'name is right'.
    $ok = $validator->isValid( $value, 'required | string:upper | pattern:[ A-Z]*', $err_msg );

Validator will alter the input value based on the rules. 

For instance, the above rule will change the string to upper cases. 
Then, validates against pattern: '[ A-Z]*'. 

note: pattern uses preg_match, internally, as preg_match( '/^{$pattern}$/', $value ) 
i.e. from the beginning to the end of the string. 

Validator has several default types.

    $value = 10;
    $ok = $validator->isValidType( $value, 'int' );



###DataIO

DataIO is a validation tool for an array of inputs, such as $_POST. 

example:

    Core::goEasy();
    $dio = Core::get( 'DataIO' );
    $dio->source( $_POST ); // default is $_POST, so this line is not necessary. 
    $dio->isValid( 'name', 'required | string:upper | pattern:[ A-Z]*' );
    $dio->isValidType( 'age', 'int' );
    $ok = $dio->errors( $err_msgs );

DataIO is useful when a validation needs another value to validate. 

example of getting a date.

    $data = array(
        'bday_y' => '1980',
        'bday_m' => '01',
        'bday_d' => '01'
    );
    $dio->source( $data );
    $dio->isValid( 'bday', [ 'multiple' => [ 'suffix' => 'y,m,d', 'connector=> '-' ] ] );
    // or you can use predefined types.
    $dio->isValidType( 'bday', 'date' );

example of checking two inputs are equal.

    $data = array(
        'email' => 'test@example.com',
        'email2' => 'test@example.com',
    );
    $dio->source( $data );
    $dio->isValidMail( 'email', 'sameWith:email2' );

