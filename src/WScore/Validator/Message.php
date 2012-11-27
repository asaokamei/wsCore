<?php
namespace WScore\Validator;

class Message
{
    /** @var array                  error message for some filters.  */
    public $filterErrMsg = array();
    
    /** @var array                  error message for each types.    */
    public $typeErrMsg = array();

    /** @var string                 error message for the value.     */
    public $message = '';

    // +----------------------------------------------------------------------+
    public function __construct()
    {
        // error messages for each filter.
        $this->filterErrMsg = array(
            'encoding'  => 'invalid encoding',
            'required'  => 'required field',
            'sameAs'    => 'value not the same',
            'sameEmpty' => 'missing value to compare',
        );
        // error messages for each type. 
        $this->typeErrMsg = array(
            'email' => 'invalid email',
            'date'  => 'invalid date',
        );
    }

    /**
     * set message on error. this message will be used ALWAYS.
     *
     * @param string $message
     */
    public function setMessage( $message ) {
        $this->message = $message;
    }
    /**
     * returns an error message from error information.
     * the error message will be:
     *   - $this->message if it is set,
     *   - filterErrMsg[ $rule ] if the value is set,
     *   - err_msg if the it is given, and
     *   - typeErrMsg[ $type ] if the value is set.
     *
     * @param array  $error
     * @param string $err_msg
     * @param null   $type
     * @return string
     */
    public function message( $error, $err_msg='', $type=null )
    {
        // is it really an error?
        if( !$error || empty( $error ) ) return '';
        // message is set.
        if( $this->message ) return $this->message;

        // find more specific error messages.
        $rule    = null;
        $option  = '';
        foreach( $error as $rule => $option ) {}
        if( !$rule ) return $err_msg;
        
        // search for filter specific error message
        if( isset( $this->filterErrMsg[ $rule ] ) ) {
            return $this->filterErrMsg[ $rule ];
        }
        // message for this specific value. 
        if( $err_msg ) {
            return $err_msg;
        }
        // message for this type, if type is set. 
        if( isset( $type ) && isset( $this->typeErrMsg[ $type ] ) ) {
            return $this->typeErrMsg[ $type ];
        }
        $err_msg = "invalid {$rule}";
        if( $option ) $err_msg .= " with {$option}";
        return $err_msg;
    }
    // +----------------------------------------------------------------------+
}