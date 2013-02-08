<?php
namespace WScore\Validator;

/**
 * must rename to... Validator or DataIO class.
 */
class NewValidator
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
     * @param Validate $validate
     * @DimInjection Fresh \WScore\Validator\Validate
     */
    public function __construct( $validate ) {
        $this->validate = $validate;
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
     * @param array|Rules $filters
     * @param null  $message
     * @return mixed
     */
    public function push( $name, $filters=array(), $message=null )
    {
        $this->find( $name, $filters, $message );
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
     * @param array|Rules $filters
     * @param null  $message
     * @return mixed
     */
    public function find( $name, $filters=array(), $message=null )
    {
        // find a value from data source.
        $value = null;
        if( array_key_exists( $name, $this->source ) ) {
            // simplest case.
            $value = $this->source[ $name ];
        }
        elseif( Utils::arrGet( $filters, 'multiple' ) ) {
            // check for multiple case i.e. Y-m-d.
            $value = Utils::prepare_multiple( $name, $this->source, $filters[ 'multiple' ] );
        }
        // prepares filter for sameWith.
        $filters = Utils::prepare_sameWith( $this, $filters );
        // now, validate this value.
        $err_msg = null;
        $ok = $this->validate->is( $value, $filters, $err_msg );
        return $ok;
    }
    // +----------------------------------------------------------------------+
}