<?php
namespace WScore\Validator;

/**
 * Validator class to validates a single value, or an array of string,
 * against same set of filters.
 * 
 * TODO: separate code for Japanese character to another module?
 * 
 * maybe split Validator class into:
 *   - Validate class, 
 *   - Message class (i18n), and 
 *   - Rules class. 
 *
 * TODO: implement more filters: code, maxlength, number, min, max, range, checkdate, mbCheckKana, sameAs, sameEmtpy
 *
 * TODO: more types with err_msg: text, code, mail, number, integer, float, date, time, etc.
 */

class Validator
{
    /** @var array        order of filterOptions to apply     */
    public $filterOrder = array();

    /** @var array        predefined filter filter set        */
    public $filterTypes = array();

    /** @var array        error message for each filter       */
    public $filterErrMsg = array();

    /** @var null|\WScore\Validator\Filter                    */
    protected $filter = null;

    // +----------------------------------------------------------------------+
    /**
     * @param Filter $filter
     * @DimInjection Get \WScore\Validator\Filter
     */
    public function __construct( $filter )
    {
        $this->filter = $filter;
        // define order of filterOptions when applying. order can be critical when
        // modifying the string (such as capitalize before checking patterns).
        //   rule => option
        // if option is FALSE, the rule is skipped.
        $this->filterOrder = array(
            // filterOptions (modifies the value)
            'noNull'      => true,       // filters out NULL (\0) char from the value.
            'encoding'    => 'UTF-8',    // checks the encoding of value.
            'mbConvert'   => 'standard', // converts Kana set (Japanese)
            'trim'        => true,       // trims value.
            'sanitize'    => false,      // done, kind of
            'string'      => false,      // converts value to upper/lower/etc.
            'default'     => '',         // sets default if value is empty.
            // validators (only checks the value).
            'required'    => false,      // fails if value is empty.
            'loopBreak'   => true,       // done, skip validations if value is empty.
            'code'        => false,
            'maxlength'   => false,
            'pattern'     => false,      // checks pattern with preg_match.
            'number'      => false,
            'min'         => false,
            'max'         => false,
            'range'       => false,
            'checkdate'   => false,
            'mbCheckKana' => false,
            'sameAs'      => false,
        );
        // setup error messages for each filter.
        $this->filterErrMsg[ 'encoding' ] = 'invalid encoding';
        $this->filterErrMsg[ 'required' ] = 'required field';

        // filters for various types of input.
        $this->filterTypes = array(
            'binary' => array(
                'noNull:FALSE | encoding:FALSE | mbConvert:FALSE | trim:FALSE '
            ),
            'text' => array(),
            'mail' => array(
                'mbConvert:hankaku|sanitize:email|pattern:mail',
                'err_msg' => 'invalid email format'
            ),
            'date' => array(
                // TODO: think of better regular date filter rules. 
                'mbConvert:hankaku|pattern:[0-9]{4}-[0-9]{2}-[0-9]{2}',
            ),
            'dateYM' => array(),
            'time' => array(),
            'datetime' => array(),
            'tel' => array(),
            'fax' => array(),
        );
    }

    /**
     * validates a value or an array of values using standard set
     * and the given extra $filters.
     *
     * @param string|array $value
     * @param string|array $filters
     * @param null|string $err_msg
     * @return bool
     */
    public function isValid( &$value, $filters='', &$err_msg=null )
    {
        // set up filterOptions with default filter.
        $filters = $this->prepareFilter( $filters );
        $filters = array_merge( $this->filterOrder, $filters );

        return $this->validate( $value, $filters, $err_msg );
    }

    /**
     * validates a value or an array of values using standard set
     * of filters for the $type, as well as the extra $filters.
     *
     * @param string $type
     * @param string|array $value
     * @param string|array $filters
     * @param null|string $err_msg
     * @return bool
     */
    public function isValidType( $type, &$value, $filters='', &$err_msg=null )
    {
        // set up filterOptions with default filter.
        $filterType = $this->getFilterType( $type );
        $filters = $this->prepareFilter( $filters );
        $filters = array_merge( $this->filterOrder, $filterType, $filters );

        return $this->validate( $value, $filters, $err_msg );
    }

    /**
     * validates a value or an array of values for a given filters.
     * filter must be an array.
     *
     * @param string|array $value
     * @param array $filter
     * @param null|string $err_msg
     * @return bool
     */
    public function validate( &$value, $filter=array(), &$err_msg=null )
    {
        $success = true;
        if( is_array( $value ) ) 
        {
            foreach( $value as $key => &$val ) {
                $e_msg = false;
                $success &= $ok = $this->validate( $val, $filter, $e_msg );
                if( !$ok ) {
                    if( !is_array( $err_msg ) ) $err_msg = array();
                    $err_msg[ $key ] = $e_msg;
                }
            }
            return (bool) $success;
        }
        return $this->_validate( $value, $filter, $err_msg );
    }

    /**
     * do the validation for a single value.
     *
     * @param string $value
     * @param array $filter
     * @param null|string $err_msg
     * @return bool
     */
    public function _validate( &$value, $filter=array(), &$err_msg=null )
    {
        $err_msg = null;
        // loop through all the rules to validate $value.
        $loop   = 'continue';
        $success = true;
        foreach( $filter as $rule => $parameter )
        {
            // prepare to apply filter.
            if( $parameter === false ) continue; // skip rules with option as FALSE.
            if( $rule == 'err_msg' ) continue;   // ignore error message.
            $success = $this->_applyRule( $value, $rule, $parameter, $loop );
            //echo "Rule: {$rule} ok={$success} loop=" . $loop . " \n";
            if( !$success ) {
                // invalidated value! find an error message.
                if( isset( $this->filterErrMsg[ $rule ] ) ) {
                    $err_msg = $this->filterErrMsg[ $rule ];
                }
                elseif( isset( $filter['err_msg'] ) ) {
                    $err_msg = $filter['err_msg'];
                }
                else {
                    $err_msg = "invalid {$rule}";
                }
                break;
            }
            if( $loop == 'break' ) break;
        }
        return $success;
    }

    /**
     * apply the filter ($rule is filter name).
     *
     * @param $value
     * @param $rule
     * @param $parameter
     * @param $loop
     * @return bool
     * @throws \RuntimeException
     */
    public function _applyRule( &$value, $rule, $parameter, &$loop )
    {
        $method = 'filter_' . $rule;
        if( method_exists( $this->filter, $method ) ) {
            return $this->filter->$method( $value, $parameter, $loop );
        }
        if( is_callable( $method ) ) {
            return $method( $value, $parameter, $loop );
        }
        return true;
        // throw new \RuntimeException( "non-exist rule: {$rule}" );
    }

    /**
     * get filters for the variable type, i.e. type='text'.
     *
     * @param string $type
     * @return array
     */
    public function getFilterType( $type )
    {
        $filter = isset( $this->filterTypes[ $type ][0] ) ? $this->filterTypes[ $type ][0] : '';
        return $this->prepareFilter( $filter );
    }

    /**
     * prepares filter if it is in string; 'rule1:parameter1|rule2:parameter2'
     *
     * @param string|array $filter
     * @return array
     */
    public function prepareFilter( $filter )
    {
        if( empty( $filter ) ) return array();
        if( is_array( $filter ) ) return $filter;
        $filter_array = array();
        $rules = explode( '|', $filter );
        foreach( $rules as $rule ) {
            $filter = explode( ':', $rule, 2 );
            array_walk( $filter, function( &$v ) { $v = trim( $v ); } );
            if( isset( $filter[1] ) ) {
                $filter_array[ $filter[0] ] = ( $filter[1]=='FALSE' )? false: $filter[1];
            }
            else {
                $filter_array[ $filter[0] ] = true;
            }
        }
        return $filter_array;
    }
    // +----------------------------------------------------------------------+
}
