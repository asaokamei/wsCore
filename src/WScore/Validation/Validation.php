<?php
namespace WScore\Validation;

/**
 * must rename to... Validator or DataIO class.
 */
class Validation
{
    /** @var array                 source of data to read from     */
    protected $source = array();

    /** @var array                 validated and invalidated data  */
    protected $output = array();

    /** @var array                 invalidated error messages      */
    protected $errors = array();

    /** @var int                   number of errors (invalids)     */
    protected $err_num = 0;

    /** @var Validate */
    protected $validate = null;

    // +----------------------------------------------------------------------+
    /**
     * @param Validate   $validate
     * @param null|array $data
     * @DimInjection Fresh \WScore\Validation\Validate
     */
    public function __construct( $validate, $data=null ) {
        $this->validate = $validate;
        if( isset( $data ) ) $this->source = $data;
    }

    /**
     * @param array $data
     */
    public function source( $data=array() ) {
        $this->source = $data;
    }

    /**
     * @param null|string $key
     * @return array
     */
    public function pop( $key=null ) {
        if( is_null( $key ) ) {
            return $this->output;
        }
        return Utils::arrGet( $this->output, $key );
    }

    /**
     * @return array
     */
    public function popSafe() {
        $safeData = $this->output;
        $this->_findClean( $safeData, $this->errors );
        return $safeData;
    }

    /**
     * @param array      $data
     * @param array|null $error
     * @param bool       $key
     */
    public function _findClean( &$data, $error, $key=false ) {
        if( empty( $error ) ) return; // no error at all.
        foreach( $data as $key => $val ) {
            if( !array_key_exists( $key, $error ) ) {
                continue; // no error.
            }
            if( is_array( $data[ $key ] ) && is_array( $error[ $key ] ) ) {
                $this->_findClean( $data[$key], $error[$key], $key );
            }
            else {
                unset( $data[ $key ] );
            }
        }
    }

    /**
     * @param null|string $name
     * @return array|mixed
     */
    public function popError( $name=null ) {
        if( $name ) return Utils::arrGet( $this->errors, $name );
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return !$this->err_num;
    }

    // +----------------------------------------------------------------------+

    /**
     * @param string $name
     * @param array|Rules $rules
     * @param null  $message
     * @return mixed
     */
    public function push( $name, $rules=array(), $message=null )
    {
        $this->find( $name, $rules, $message );
        $this->output[ $name ] = $this->validate->value;
        if( !$this->validate->isValid ) {
            $this->errors[ $name ] = $this->validate->err_msg;
            $this->err_num ++;
            return false;
        }
        return $this->output[ $name ];
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return Validation
     */
    public function pushValue( $name, $value ) {
        $this->output[ $name ] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $error
     * @param bool|mixed $value
     * @return Validation
     */
    public function pushError( $name, $error, $value=false ) {
        $this->errors[ $name ] = $error;
        $this->err_num ++;
        if( $value !== false ) $this->output[ $name ] = $value;
        return $this;
    }
    
    /**
     * @param string $name
     * @param array|Rules $rules
     * @param null  $message
     * @return mixed
     */
    public function find( $name, $rules=array(), $message=null )
    {
        // find a value from data source.
        $value = null;
        if( array_key_exists( $name, $this->source ) ) {
            // simplest case.
            $value = $this->source[ $name ];
        }
        elseif( Utils::arrGet( $rules, 'multiple' ) ) {
            // check for multiple case i.e. Y-m-d.
            $value = Utils::prepare_multiple( $name, $this->source, $rules[ 'multiple' ] );
        }
        // prepares filter for sameWith.
        $rules = Utils::prepare_sameWith( $this, $rules );
        // now, validate this value.
        $ok = $this->validate->is( $value, $rules, $message );
        return $ok;
    }
    // +----------------------------------------------------------------------+
}