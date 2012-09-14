<?php
namespace wsCore\Validator;

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
    // +----------------------------------------------------------------------+
    public function __construct()
    {
        $this->filterOrder = array(
            'multiple'    => FALSE, // combine multiple values into one.
            'sameWith'    => FALSE, // compare with another value.
            'sameEmpty'   => FALSE, // checks if another value is empty.
        );
        $this->filterOptions = array(
            'sameEmpty' => array( 'err_msg' => 'missing value to compare' ),
        );
        $this->filterTypes = array(
            'date' => array(
                'multiple' => array(
                    'suffix' => 'y,m,d',
                    'connector' => '-',
                ),
            ),
            'dateYM' => array(
                'suffix' => 'y,m',
                'connector' => '-',
            ),
            'time' => array(
                'suffix' => 'h,i,s',
                'connector' => ':',
            ),
            'datetime' => array(
                'suffix' => 'y,m,d,h,i,s',
                'format' => '%04d-%02d-%02d %02d:%02d:%02d',
            ),
            'tel' => array(
                'suffix' => '1,2,3',
                'connector' => '-',
            ),
            'fax' => array(
                'suffix' => '1,2,3',
                'connector' => '-',
            ),
        );
    }

    /**
     * original _find method which takes care of sameAs filter.
     * should rewrite this.
     *
     * @param $data
     * @param $name
     * @param bool $filters
     * @param bool $type
     * @return bool|mixed|null|string
     */
    function _find( $data, $name, &$filters=FALSE, $type=FALSE ) {
        if( $type !== FALSE ) {
            $filters = self::_getFilter( $filters, $type );
        }
        $value = NULL;
        if( isset( $data[ $name ] ) ) {
            // simplest case.
            $value = $data[ $name ];
            if( !Util::isValue( $value ) ) $value = self::DEFAULT_EMPTY_VALUE;
        }
        else
            if( isset( $filters[ 'multiple' ] ) && $filters[ 'multiple' ] !== FALSE ) {
                // case to read such as Y-m-d in three different values.
                $value = self::_multiple( $data, $name, $filters[ 'multiple' ] );
            }
        if( $value !== NULL &&
            isset( $filters[ 'samewith' ] ) && $filters[ 'samewith' ] !== FALSE ) {
            // compare with other inputs as specified by samewith.
            $sub_filters = $filters;
            $sub_filters[ 'samewith' ] = FALSE;
            $sub_name  = $filters[ 'samewith' ];
            $sub_value = self::_find( $data, $sub_name, $sub_filters );
            if( $sub_value ) {
                $filters[ 'sameas' ] = $sub_value;
            }
            else {
                $filters[ 'sameempty' ] = TRUE;
            }
        }
        return $value;
    }

    /**
     * original implementation of _multiple filter.
     * should rewrite this.
     *
     * @param $data
     * @param $name
     * @param $option
     * @return bool|mixed|string
     */
    function _multiple( $data, $name, $option )
    {
        $sep  = '_';
        $con  = '-';
        if( isset( $option['separator'] ) ) {
            $sep = $option['separator'];
        }
        if( isset( $option['connecter'] ) ) {
            $con = $option['connecter'];
        }
        $lists = array();
        $found = FALSE;
        foreach( $option[ 'suffix' ] as $sfx ) {
            $name_sfx = $name . $sep . $sfx;
            $val = Util::getValue( $data, $name_sfx, FALSE );
            if( $val !== FALSE ) {
                $lists[] = $data[ $name_sfx ];
                $found   = self::DEFAULT_EMPTY_VALUE;
            }
        }
        if( $found === FALSE ) {
            // keep $found as FALSE;
        }
        else
            if( isset( $option[ 'sformat' ] ) ) {
                $param = array_merge( array( $option[ 'sformat' ] ), $lists );
                $found = call_user_func_array( 'sprintf', $param );
            }
            else {
                $found = implode( $con, $lists );
            }
        if( WORDY ) echo "_multiple( \$data, $name, \$option ) => $found \n";
        return $found;
    }
}