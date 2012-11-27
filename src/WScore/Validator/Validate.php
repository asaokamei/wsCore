<?php
namespace WScore\Validator;

class Validate
{
    /** @var \WScore\Validator\Filter */
    protected $filter;

    /** @var Message */
    protected $message;

    /** @var array|Rules */
    protected $rules;

    public $isValid;

    public $value;
    
    public $err_msg;

    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Validator\Filter  $filter
     * @param \WScore\Validator\Message $message
     * @DimInjection Get \WScore\Validator\Filter
     * @DimInjection Get \WScore\Validator\Message
     */
    public function __construct( $filter, $message )
    {
        $this->filter  = $filter;
        $this->message = $message;
    }

    /**
     * initializes internal values.
     *
     * @param null|string $message
     */
    protected function init( $message=null )
    {
        $this->value   = null;
        $this->isValid = true;
        $this->err_msg = null;
        $this->message->setMessage( $message );
    }

    /**
     * generates error message from filter's error information.
     *
     * @param array $error
     * @return string
     */
    public function getMessage( $error )
    {
        if( !$this->message ) return $error;
        $type = ( is_object( $this->rules ) && $this->rules instanceof \WScore\Validator\Rules )? $this->rules->type : null;
        return $this->message->message( $error, $this->filter->err_msg, $type );
    }

    // +----------------------------------------------------------------------+
    /**
     * @param string|array $value
     * @param array $rules
     * @param null|string   $message
     * @return bool
     */
    public function is( $value, $rules=array(), $message=null ) {
        return $this->validate( $value, $rules, $message );
    }
    /**
     * validates a value or an array of values for a given filters.
     * filter must be an array.
     *
     * @param string|array $value
     * @param array|Rules  $rules
     * @param null|string   $message
     * @return bool
     */
    public function validate( $value, $rules=array(), $message=null )
    {
        $this->init( $message );
        $this->rules = $rules;
        if( is_array( $value ) )
        {
            $this->value   = array();
            $this->err_msg = array();
            foreach( $value as $key => $val ) 
            {
                $success = $this->applyFilters( $val, $rules );
                $this->value[ $key ] = $this->filter->value;
                if( !$success ) {
                    $this->err_msg[ $key ] = $this->filter->error;
                }
                $this->isValid &= ( $success === true );
            }
            $this->isValid = (bool) $this->isValid;
            return $this->isValid;
        }
        $this->isValid = $this->applyFilters( $value, $rules );
        $this->err_msg = $this->filter->error;
        $this->value   = $this->filter->value;
        return $this->isValid;
    }

    /**
     * do the validation for a single value.
     *
     * @param string $value
     * @param array  $rules
     * @return bool
     */
    public function applyFilters( $value, $rules )
    {
        $this->filter->setup( $value );
        $success = true;
        // loop through all the rules to validate $value.
        foreach( $rules as $rule => $parameter )
        {
            // some filters are not to be applied...
            if( $parameter === false ) continue; // skip rules with option as FALSE.
            // apply filter.
            $method = 'filter_' . $rule;
            if( method_exists( $this->filter, $method ) ) {
                $this->filter->$method( $parameter );
            }
            // got some error. 
            if( $this->filter->error ) {
                $this->filter->error = $this->getMessage( $this->filter->error );
                $success = false;
                break;
            }
            // loop break. 
            if( $this->filter->break ) break;
        }
        return $success;
    }
    // +----------------------------------------------------------------------+
}