<?php
namespace WScore\Validator;

class Message
{
    /** @var array                  error message for some filters.  */
    public $filterErrMsg = array();
    
    /** @var string                 error message for the value.     */
    public $message = '';
    
    /** @var array                  error message for each types.    */
    public $typeErrMsg = array();
    
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
     * @param null|string $message
     * @return Message
     */
    public function __invoke( $message=null ) {
        $this->setMessage( $message );
        return $this;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function setMessage( $message ) {
        $this->message = $message;
        return $this;
    }

    /**
     * @param array $error
     * @param null  $type
     * @return string
     */
    public function message( $error, $type=null )
    {
        $message = '';
        $rule    = null;
        if( empty( $error ) ) return $message;
        foreach( $error as $rule => $option ) {}
        if( !$rule ) return $message;
        
        // search for filter specific error message
        if( isset( $this->filterErrMsg[ $rule ] ) ) {
            return $this->filterErrMsg[ $rule ];
        }
        // message for this specific value. 
        if( isset( $this->message ) ) {
            return $this->message;
        }
        // message for this type, if type is set. 
        if( isset( $type ) && isset( $this->typeErrMsg[ $type ] ) ) {
            return $this->typeErrMsg[ $type ];
        }
        return "invalid input";
    }
    
}