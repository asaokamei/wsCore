<?php
namespace WScore\Validator;

/**
 * Validator class to validates a single value, or an array of string,
 * against same set of filters.
 * TODO: separate code for Japanese character to another module?
 *
 * TODO: implement more filters: code, maxlength, number, min, max, range, checkdate, mbCheckKana, sameAs, sameEmtpy
 *
 * TODO: more types with err_msg: text, code, mail, number, integer, float, date, time, etc.
 */

class Validator
{
    /** @var array        definition of filterOptions         */
    public $filterOptions = array();

    /** @var array        order of filterOptions to apply     */
    public $filterOrder = array();

    /** @var array        predefined filter filter set        */
    public $filterTypes = array();

    public static $charCode = 'UTF-8';
    // +----------------------------------------------------------------------+
    public function __construct()
    {
        // define order of filterOptions when applying. order can be critical when
        // modifying the string (such as capitalize before checking patterns).
        //   rule => option
        // if option is FALSE, the rule is skipped.
        $this->filterOrder = array(
            // filterOptions (modifies the value)
            'noNull'      => TRUE,       // filters out NULL (\0) char from the value.
            'encoding'    => 'UTF-8',    // checks the encoding of value.
            'mbConvert'   => 'standard', // converts Kana set (Japanese)
            'trim'        => TRUE,       // trims value.
            'sanitize'    => FALSE,      // done, kind of
            'string'      => FALSE,      // converts value to upper/lower/etc.
            'default'     => '',         // sets default if value is empty.
            // validators (only checks the value).
            'required'    => FALSE,      // fails if value is empty.
            'loopBreak'   => TRUE,       // done, skip validations if value is empty.
            'code'        => FALSE,
            'maxlength'   => FALSE,
            'pattern'     => FALSE,      // checks pattern with preg_match.
            'number'      => FALSE,
            'min'         => FALSE,
            'max'         => FALSE,
            'range'       => FALSE,
            'checkdate'   => FALSE,
            'mbCheckKana' => FALSE,
            'sameAs'      => FALSE,
        );
        $this->filterOptions = array(
            'trim' => array( 'myTrim' ),
            'sanitize' => array(
                'mail'   => FILTER_SANITIZE_EMAIL,
                'float'  => FILTER_SANITIZE_NUMBER_FLOAT,
                'int'    => FILTER_SANITIZE_NUMBER_INT,
                'url'    => FILTER_SANITIZE_URL,
                'string' => FILTER_SANITIZE_STRING,
            ),
            'pattern' => array( 'pattern',
                'number' => '[0-9]+',
                'int'    => '[-0-9]+',
                'float'  => '[-.0-9]+',
                'code'   => '[-_0-9a-zA-Z]+',
                'mail'   => '[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]+',
            ),
            'sameAs' => array( function( $v, $p ) { return $v===$p; } ),
        );
        // setup error messages for each filter.
        $this->filterOptions[ 'encoding' ][ 'err_msg' ] = 'invalid encoding';
        $this->filterOptions[ 'required' ][ 'err_msg' ] = 'required field';

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
    public function isValid( &$value, $filters='', &$err_msg=NULL )
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
    public function isValidType( $type, &$value, $filters='', &$err_msg=NULL )
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
    public function validate( &$value, $filter=array(), &$err_msg=NULL )
    {
        $success = TRUE;
        if( is_array( $value ) ) 
        {
            foreach( $value as $key => &$val ) {
                $e_msg = FALSE;
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
    public function _validate( &$value, $filter=array(), &$err_msg=NULL )
    {
        $err_msg = NULL;
        // loop through all the rules to validate $value.
        $loop   = 'continue';
        $success = TRUE;
        foreach( $filter as $rule => $parameter )
        {
            // prepare to apply filter.
            if( $parameter === FALSE ) continue; // skip rules with option as FALSE.
            if( $rule == 'err_msg' ) continue;   // ignore error message.
            $success = $this->_applyRule( $value, $rule, $parameter, $loop );
            //echo "Rule: {$rule} ok={$success} loop=" . $loop . " \n";
            if( !$success ) {
                // invalidated value! find an error message.
                if( isset( $this->filterOptions[ $rule ]['err_msg'] ) ) {
                    $err_msg = $this->filterOptions[ $rule ]['err_msg'];
                }
                elseif( isset( $filter['err_msg'] ) ) {
                    $err_msg = $filter['err_msg'];
                }
                else {
                    $err_msg = "invalidated with rule={$rule}";
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
        // initial value.
        $method = $rule;
        // get options for this filter if exists.
        if( isset( $this->filterOptions[ $rule ] ) )
        {
            // overwrite method; how to run the rule.
            if( isset( $this->filterOptions[ $rule ][0] ) ) {
                $method  = $this->filterOptions[ $rule ][0];
            }
            // overwrite parameter.
            if( isset( $this->filterOptions[ $rule ][$parameter] ) ) {
                $parameter = $this->filterOptions[ $rule ][$parameter];
            }
        }
        if( is_callable( $method ) ) {
            return $method( $value, $parameter, $loop );
        }
        $method = 'filter_' . $method;
        if( method_exists( $this, $method ) ) {
            return $this->$method( $value, $parameter, $loop );
        }
        throw new \RuntimeException( "non-exist rule: {$rule}" );
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
                $filter_array[ $filter[0] ] = ( $filter[1]=='FALSE' )? FALSE: $filter[1];
            }
            else {
                $filter_array[ $filter[0] ] = TRUE;
            }
        }
        return $filter_array;
    }
    // +----------------------------------------------------------------------+
    //  filter definitions (filters that alters the value).
    // +----------------------------------------------------------------------+

    public function filter_noNull( &$v ) { 
        $v = str_replace( "\0", '', $v ); 
        return TRUE; 
    }
    
    public function filter_myTrim( &$v ) { 
        $v = trim( $v );
        return TRUE;
    }
    
    public function sanitize( &$v, $p ) { 
        $v = filter_var( $v, $p ); 
        return !!$v; 
    }
    
    public function filter_encoding( &$v, $p ) {
        $code = ( empty( $p ) ) ? static::$charCode: $p;
        if( mb_check_encoding( $v, $code ) ) {
            return TRUE;
        }
        $v = ''; // overwrite invalid encode string.
        return FALSE;
    }

    public function filter_mbConvert( &$v, $p ) {
        static $option = NULL;
        if( !isset( $option ) ) $option = array(
            'hankaku' => 'aks',
            'han_kana' => 'kh',
            'zen_hira' => 'HVc',
            'zen_kana' => 'KVC',
        );
        $convert = isset( $option[$p] ) ? $option[$p] : 'KV';
        $v = mb_convert_kana( $v, $convert, static::$charCode );
        return TRUE;
    }

    public function filter_sanitize( &$v, $p ) {
        static $option = NULL;
        if( !isset( $option ) ) $option = array(
            'mail' => FILTER_SANITIZE_EMAIL,
        );
        return TRUE;
    }
    
    public function filter_string( &$v, $p ) {
        if( $p == 'lower' ) {
            $v = strtolower( $v );
        }
        elseif( $p == 'upper' ) {
            $v = strtoupper( $v );
        }
        elseif( $p == 'capital' ) {
            $v = ucwords( $v );
        }
        return TRUE;
    }
    
    public function filter_default( &$v, $p, &$loop=NULL ) {
        if( !$v && "" == "$v" ) { // no value. set default...
            $v = $p;
        }
        return TRUE; // but it is not an error. 
    }
    // +----------------------------------------------------------------------+
    //  filter definitions (filters for validation).
    // +----------------------------------------------------------------------+

    public function filter_required( $v, $p, &$loop=NULL ) {
        if( "$v" != '' ) { // it has some value in it. OK.
            return TRUE;
        }
        // now, the value is empty. check if it is "required".
        return FALSE;
    }

    /**
     * breaks loop if value is empty by returning $loop='break'.
     * validation is not necessary for empty value.
     *
     * @param $v
     * @param $p
     * @param $loop
     * @return bool
     */
    public function filter_loopBreak( $v, $p, &$loop ) {
        if( "$v" == '' ) { // value is really empty. break the loop.
            $loop = 'break'; // skip subsequent validations for empty values.
        }
        return TRUE;
    }

    public function filter_pattern( $v, $p ) {
        $ok = preg_match( "/^{$p}\$/", $v );
        return !!$ok;
    }
    // +----------------------------------------------------------------------+
}
