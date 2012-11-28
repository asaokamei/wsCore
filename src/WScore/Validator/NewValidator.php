<?php
namespace WScore\Validator;

/**
 * must rename to... Validator or DataIO class.
 */
class NewValidator
{
    /** @var array                 source of data to read from     */
    private $source = array();

    /** @var array                 validated and invalidated data  */
    private $data = array();

    /** @var array                 invalidated error messages      */
    private $errors = array();

    /** @var int                   number of errors (invalids)     */
    private $err_num = 0;

    /** @var Validate */
    private $validate = null;

    // +----------------------------------------------------------------------+
    /**
     * @param $validate
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
            return $this->data;
        }
        return $this->arrGet( $this->data, $key );
    }

    /**
     * @return array
     */
    public function popSafe() {
        $safeData = $this->data;
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
        if( $name ) return $this->arrGet( $this->errors, $name );
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return !$this->err_num;
    }

    public function arrGet( $arr, $key, $default=null ) {
        return array_key_exists( $key, $arr ) ? $arr[ $key ] : $default;
    }
    // +----------------------------------------------------------------------+

    /**
     * @param string $name
     * @param array $filters
     * @param null  $message
     * @return mixed
     */
    public function push( $name, $filters=array(), $message=null )
    {
        $this->find( $name, $filters, $message );
        $this->data[ $name ] = $this->validate->value;
        if( !$this->validate->isValid ) {
            $this->errors[ $name ] = $this->validate->err_msg;
            $this->err_num ++;
            return false;
        }
        return $this->data[ $name ];
    }

    /**
     * @param string $name
     * @param array $filters
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
        elseif( $this->arrGet( $filters, 'multiple' ) ) {
            // check for multiple case i.e. Y-m-d.
            $value = $this->prepare_multiple( $name, $filters[ 'multiple' ] );
        }
        // prepares filter for sameWith.
        $filters = $this->prepare_sameWith( $filters );
        // now, validate this value.
        $err_msg = null;
        $ok = $this->validate->is( $value, $filters, $err_msg );
        return $ok;
    }

    // +----------------------------------------------------------------------+
    //  multiple inputs.
    // +----------------------------------------------------------------------+
    /**
     * @var array   options for multiple preparation.
     */
    public $multiples = array(
        'date'     => array( 'suffix' => 'y,m,d', 'connector' => '-', ),
        'YMD'      => array( 'suffix' => 'y,m,d', 'connector' => '-', ),
        'YM'       => array( 'suffix' => 'y,m',   'connector' => '-', ),
        'time'     => array( 'suffix' => 'h,i,s', 'connector' => ':', ),
        'His'      => array( 'suffix' => 'h,i,s', 'connector' => ':', ),
        'hi'       => array( 'suffix' => 'h,i',   'connector' => ':', ),
        'datetime' => array( 'suffix' => 'y,m,d,h,i,s', 'format' => '%04d-%02d-%02d %02d:%02d:%02d', ),
        'tel'      => array( 'suffix' => '1,2,3',   'connector' => '-', ),
        'credit'   => array( 'suffix' => '1,2,3,4', 'connector' => '', ),
        'amex'     => array( 'suffix' => '1,2,3',   'connector' => '', ),
    );

    /**
     * prepares for validation by creating a value from multiple value.
     *
     * @param string $name
     * @param string|array $option
     * @return mixed|null|string
     */
    public function prepare_multiple( $name, $option )
    {
        // get options.
        if( is_string( $option ) ) {
            $option = $this->multiples[ $option ];
        }
        $sep = array_key_exists( 'separator', $option ) ? $option[ 'separator' ]: '_';
        $con = array_key_exists( 'connector', $option ) ? $option[ 'connector' ]: '-';
        // find multiples values from suffix list.
        $lists = array();
        $suffix = explode( ',', $option[ 'suffix' ] );
        foreach( $suffix as $sfx ) {
            $name_sfx = $name . $sep . $sfx;
            if( array_key_exists( $name_sfx, $this->source ) ) {
                $lists[] = $this->source[ $name_sfx ];
            }
        }
        // merge the found list into one value.
        $found = null; // default is null if list was not found.
        if( !empty( $lists ) )
        {
            // found format using sprintf.
            if( isset( $option[ 'format' ] ) ) {
                $param = array_merge( array( $option[ 'format' ] ), $lists );
                $found = call_user_func_array( 'sprintf', $param );
            }
            else {
                $found = implode( $con, $lists );
            }
        }
        return $found;
    }

    // +----------------------------------------------------------------------+
    /**
     * prepares filter for sameWith rule.
     * get another value to compare in sameWith, and compare it with the value using sameAs rule.
     *
     * @param array $filters
     * @return array
     */
    public function prepare_sameWith( $filters )
    {
        if( !$this->arrGet( $filters, 'sameWith' ) ) return $filters;
        // find the same with value.
        $sub_name = $filters[ 'sameWith' ];
        if( is_object( $filters ) ) {
            $sub_filter = clone $filters;
        } else {
            $sub_filter = $filters;
        }
        $sub_filter[ 'sameWith' ] = false;
        $sub_filter[ 'required' ] = false;
        $value = null;
        $this->find( $sub_name, $sub_filter );

        // reset sameWith filter, and set same{As|Empty} filter.
        $filters[ 'sameWith' ] = false;
        if( $value ) {
            $filters[ 'sameAs' ] = $value;
        }
        else {
            $filters[ 'sameEmpty' ] = true;
        }
        $filters[ 'sameWith' ] = false;
        return $filters;
    }
    // +----------------------------------------------------------------------+
}