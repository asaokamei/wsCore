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
    private $validator = NULL;
    // +----------------------------------------------------------------------+
    /**
     * @param $validator
     * @DimInjection Fresh \WScore\Validator\Validator
     */
    public function __construct( $validator ) 
    {
        $this->validator = $validator;
        $this->filterOrder = array(
            'multiple'    => FALSE, // combine multiple values into one.
            'sameWith'    => FALSE, // compare with another value.
            'sameEmpty'   => FALSE, // checks if another value is empty.
        );
        $this->filterOptions = array(
            'sameEmpty' => array( 'err_msg' => 'missing value to compare' ),
            'multiple'  => array(
                'date' => array( 'suffix' => 'y,m,d',  'connector' => '-', ),
                'YMD'  => array( 'suffix' => 'y,m,d',  'connector' => '-', ),
                'YM'   => array( 'suffix' => 'y,m',    'connector' => '-', ),
                'time' => array( 'suffix' => 'h,i,s',  'connector' => ':', ),
                'His'  => array( 'suffix' => 'h,i,s',  'connector' => ':', ),
                'hi'   => array( 'suffix' => 'h,i',    'connector' => ':', ),
                'datetime' => array( 'suffix' => 'y,m,d,h,i,s', 'format' => '%04d-%02d-%02d %02d:%02d:%02d', ),
                'tel'    => array( 'suffix' => '1,2,3', 'connector' => '-', ),
                'credit' => array( 'suffix' => '1,2,3,4', 'connector' => '', ),
                'amex'   => array( 'suffix' => '1,2,3', 'connector' => '', ),
            ),
        );
        $this->filterTypes = array(
            'date' => array(
                'multiple' => 'date',
            ),
            'time' => array(
                'multiple' => 'time',
            ),
            'datetime' => array(
                'multiple' => 'datetime',
            ),
            'tel' => array(
                'multiple' => 'tel',
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
    public function pushValue( $name, $filters='', &$value=NULL )
    {
        $filters = $this->validator->prepareFilter( $filters );
        $filters = array_merge( $this->filterOrder, $filters );
        $value = NULL;
        $ok = $this->validate( $name, $value, NULL, $filters );
        if( !$ok ) $value = FALSE;
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string|array $filters
     * @param mixed $value
     * @return DataIO
     */
    public function push( $name, $type, $filters='', &$value=NULL )
    {
        $filterType = $this->getFilterType( $type );
        $filters = $this->validator->prepareFilter( $filters );
        $filters = array_merge( $this->filterOrder, $filterType, $filters );
        $value = NULL;
        $ok = $this->validate( $name, $value, $type, $filters );
        if( !$ok ) $value = FALSE;
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
    public function validate( $name, &$value, $type=NULL, &$filters=array(), &$err_msg=NULL )
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
    public function pop( $key=NULL ) {
        if( is_null( $key ) ) {
            return $this->data;
        } 
        elseif( array_key_exists( $key, $this->data ) ) {
            return $this->data[ $key ];
        }
        return NULL;
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
    public function _findClean( &$data, $error, $key=FALSE ) {
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
    function _find( $name, &$value, $type=NULL, &$filters=array(), &$err_msg=NULL ) 
    {
        // find a value from $data. 
        $value = NULL;
        if( array_key_exists( $name, $this->source ) ) {
            // simplest case.
            $value = $this->source[ $name ];
        }
        elseif( isset( $filters[ 'multiple' ] ) && $filters[ 'multiple' ] !== FALSE ) {
            // check for multiple case i.e. Y-m-d.
            $value = self::_multiple( $name, $filters[ 'multiple' ] );
        }
        // check for sameWith filter. 
        if( $value !== NULL &&
            isset( $filters[ 'sameWith' ] ) && $filters[ 'sameWith' ] !== FALSE ) 
        {
            // compare with other inputs as specified by sameWith.
            $sub_value   = NULL;
            $sub_name    = $filters[ 'sameWith' ];
            /** @var $sub_filters array */
            $sub_filters = $filters; // use same filter as original. 
            $sub_filters[ 'sameWith' ] = FALSE; // but no sameWith. 
            $sub_filters[ 'required' ] = FALSE; // and not required. 
            self::_find( $sub_name, $sub_value, $type, $sub_filters );
            if( $sub_value ) {
                $filters[ 'sameAs' ] = $sub_value;
            }
            else {
                $filters[ 'sameEmpty' ] = TRUE;
            }
        }
        // now, validate this value.
        $ok = $this->validator->isValidType( $type, $value, $filters, $err_msg );
        return $ok;
    }

    /**
     * original implementation of _multiple filter.
     * should rewrite this.
     *
     * @param string $name
     * @param array $option
     * @return bool|mixed|string
     */
    function _multiple( $name, $option )
    {
        $sep  = '_';
        $con  = '-';
        if( isset( $option['separator'] ) ) {
            $sep = $option['separator'];
        }
        if( isset( $option['connector'] ) ) {
            $con = $option['connector'];
        }
        $lists = array();
        foreach( $option[ 'suffix' ] as $sfx ) {
            $name_sfx = $name . $sep . $sfx;
            if( array_key_exists( $name_sfx, $this->source ) ) {
                $lists[] = $this->source[ $name_sfx ];
            }
        }
        $found = NULL;
        if( !empty( $lists ) ) {
            // found something...
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