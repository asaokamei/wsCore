<?php
namespace WScore\Validator;

/**
 * Data Input Output, i.e. DIO module!
 * input and output data validation and store.
 *
 */
class DataIO
{
    /** @var array                 source of data to read from     */
    private $source = array();

    /** @var array                 validated and invalidated data  */
    private $data = array();

    /** @var array                 invalidated error messages      */
    private $errors = array();

    /** @var int                   number of errors (invalids)     */
    private $err_num = 0;

    /** @var array                 filterOrder for DataIO              */
    private $filterOrder = array();

    /** @var array                 options of filter                   */
    private $filterOptions = array();

    /** @var array                 preset filterOrder for data types   */
    private $filterTypes = array();
    
    /** @var Validator */
    private $validator = null;
    // +----------------------------------------------------------------------+
    /**
     * @param $validator
     * @DimInjection Fresh \WScore\Validator\Validator
     */
    public function __construct( $validator ) 
    {
        $this->validator = $validator;
        $this->filterOrder = array(
            'multiple'    => false, // combine multiple values into one.
            'sameWith'    => false, // compare with another value.
            'sameEmpty'   => false, // checks if another value is empty.
        );
        $this->filterOptions = array(
            'sameEmpty' => array( 'err_msg' => 'missing value to compare' ),
        );
        $this->filterTypes = array(
            'date' => array(
                'multiple:date',
            ),
            'time' => array(
                'multiple:time',
            ),
            'datetime' => array(
                'multiple:datetime',
            ),
            'tel' => array(
                'multiple:tel',
            ),
        );
    }

    /**
     * @param array|\WScore\DbAccess\Entity_Interface $data
     */
    public function source( $data=array() ) {
        $this->source = $data;
    }
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @param string|array $filters
     * @param mixed $value
     * @return DataIO
     */
    public function pushValue( $name, $filters='', &$value=null )
    {
        $filters = $this->validator->prepareFilter( $filters );
        $filters = array_merge( $this->filterOrder, $filters );
        $value = null;
        $ok = $this->validate( $name, $value, null, $filters );
        if( !$ok ) $value = false;
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string|array $filters
     * @param mixed $value
     * @return DataIO
     */
    public function push( $name, $type, $filters='', &$value=null )
    {
        $filterType = $this->getFilterType( $type );
        $filters = $this->validator->prepareFilter( $filters );
        $filters = array_merge( $this->filterOrder, $filterType, $filters );
        $value = null;
        $ok = $this->validate( $name, $value, $type, $filters );
        if( !$ok ) $value = false;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param null|string $type
     * @param array $filters
     * @param mixed $err_msg
     * @return bool
     */
    public function validate( $name, &$value, $type=null, &$filters=array(), &$err_msg=null )
    {
        $ok = $this->_find( $name, $value, $type, $filters, $err_msg );
        $this->data[ $name ] = $value;
        if( !$ok ) {
            $this->errors[ $name ] = $err_msg;
            $this->err_num++;
        }
        return $ok;
    }

    /**
     * @param $type
     * @return array
     */
    public function getFilterType( $type )
    {
        $filter = isset( $this->filterTypes[ $type ][0] ) ? $this->filterTypes[ $type ][0] : '';
        return $this->validator->prepareFilter( $filter );
    }

    /**
     * @param null|string $key
     * @return array
     */
    public function pop( $key=null ) {
        if( is_null( $key ) ) {
            return $this->data;
        } 
        elseif( array_key_exists( $key, $this->data ) ) {
            return $this->data[ $key ];
        }
        return null;
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
     * @param array $data
     * @param array|null $error
     * @param bool $key
     */
    public function _findClean( &$data, $error, $key=false ) {
        if( empty( $error ) ) return; // no error at all.
        if( is_array( $data ) ) {
            foreach( $data as $key => $val ) {
                if( !array_key_exists( $key, $error ) ) {
                    continue; // no error.
                }
                if( is_array( $data[ $key ] ) ) {
                    $this->_findClean( $data[$key], $error[$key], $key );
                }
                else {
                    unset( $data[ $key ] );
                }
            }
        }
    }

    /**
     * @param array $errors
     * @return int
     */
    public function popErrors( &$errors=array() ) {
        $errors = $this->errors;
        return $this->err_num;
    }

    /**
     * @param null|string $name
     * @return array|mixed
     */
    public function popError( $name=null ) {
        if( $name ) return \WScore\Utilities\Tools::getKey( $this->errors, $name, null );
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
     * finds value from $source, and stores the found value in $data, and 
     * error message in $errors. 
     * 
     * This routine takes care of multiple and sameWith filters, and 
     * use validator to validate the value. 
     *
     * @param string       $name
     * @param string|array $value
     * @param string       $type
     * @param array        $filters
     * @param              $err_msg
     * @return bool
     */
    public function _find( $name, &$value, $type=null, $filters=array(), &$err_msg=null )
    {
        // find a value from data source. 
        $value = null;
        if( array_key_exists( $name, $this->source ) ) {
            // simplest case.
            $value = $this->source[ $name ];
        }
        elseif( isset( $filters[ 'multiple' ] ) && $filters[ 'multiple' ] !== false ) {
            // check for multiple case i.e. Y-m-d.
            $value = $this->prepare_multiple( $name, $filters[ 'multiple' ] );
        }
        // prepares filter for sameWith. 
        $filters = $this->prepare_sameWith( $type, $filters );
        // now, validate this value.
        $err_msg = null;
        $ok = $this->validator->isValidType( $type, $value, $filters, $err_msg );
        return $ok;
    }
    /**
     * prepares filter for sameWith rule.
     * get another value to compare in sameWith, and compare it with the value using sameAs rule.
     *
     * @param string $type
     * @param array $filters
     * @return array
     */
    public function prepare_sameWith( $type, $filters )
    {
        if( !isset( $filters[ 'sameWith' ] ) || $filters[ 'sameWith' ] == false ) return $filters;
        // find the same with value. 
        $sub_name = $filters[ 'sameWith' ];
        $sub_filter = $filters;
        $sub_filter[ 'sameWith' ] = false;
        $sub_filter[ 'required' ] = false;
        $value = null;
        $this->_find( $sub_name, $value, $type, $sub_filter );
        
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
}