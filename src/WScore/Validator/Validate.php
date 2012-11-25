<?php
namespace WScore\Validator;

class Validate
{
    /** @var \WScore\Validator\Filter */
    protected $filter;
    
    /** @var string|object */
    protected $message;
    
    public $isValid;

    public $value;
    
    public $err_msg;

    /**
     * @param Filter $filter
     * @DimInjection Get \WScore\Validator\Filter
     */
    public function __construct( $filter )
    {
        $this->filter = $filter;
    }

    /**
     * initializes internal values. 
     */
    protected function init( $message=null ) 
    {
        $this->value   = null;
        $this->isValid = true;
        $this->err_msg = null;
        $this->message = $message;
    }

    public function message( $error )
    {
        if( is_string( $this->message ) ) {
            return $this->message;
        }
        if( is_object( $this->message ) && method_exists( $this->message, 'getMessage' ) ) {
            return $this->message->getMessage( $this->err_msg );
        }
        return $error;
    }
    /**
     * @param string|array $value
     * @param array $filter
     * @param null|\Closure   $message
     * @return bool
     */
    public function __invoke( $value, $filter=array(), $message=null ) {
        return $this->validate( $value, $filter, $message );
    }
    
    public function is( $value, $filter=array(), $message=null ) {
        return $this->validate( $value, $filter, $message );
    }
    /**
     * validates a value or an array of values for a given filters.
     * filter must be an array.
     *
     * @param string|array $value
     * @param array        $filter
     * @param null|\Closure   $message
     * @return bool
     */
    public function validate( $value, $filter=array(), $message=null )
    {
        $this->init( $message );
        if( is_array( $value ) )
        {
            $this->value   = array();
            $this->err_msg = array();
            foreach( $value as $key => $val ) 
            {
                $success = $this->validate( $val, $filter );
                $this->value[ $key ] = $this->filter->value;
                if( !$success ) {
                    $this->err_msg[ $key ] = $this->filter->error;
                }
                $this->isValid &= ( $success === true );
            }
            return (bool) $this->isValid;
        }
        $this->isValid = $this->applyFilters( $value, $filter );
        $this->err_msg = $this->filter->error;
        $this->value   = $this->filter->value;
        return $this->isValid;
    }

    /**
     * do the validation for a single value.
     *
     * @param string $value
     * @param array  $filter
     * @param null|\Closure   $message
     * @return bool
     */
    public function applyFilters( $value, $filter, $message=null )
    {
        $this->filter->setup( $value );
        $success = true;
        // loop through all the rules to validate $value.
        foreach( $filter as $rule => $parameter )
        {
            // some filters are not to be applied...
            if( $parameter === false ) continue; // skip rules with option as FALSE.
            if( $rule == 'err_msg'   ) continue; // ignore error message.
            // apply filter. 
            $method = 'filter_' . $rule;
            if( method_exists( $this->filter, $method ) ) {
                $this->filter->$method( $parameter );
            }
            // got some error. 
            if( $this->filter->error ) {
                if( $message ) {
                    $this->filter->error = $this->message( $this->filter->error );
                }
                $success = false;
                break;
            }
            // loop break. 
            if( $this->filter->break ) break;
        }
        return $success;
    }

}