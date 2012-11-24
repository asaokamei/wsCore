<?php
namespace WScore\Validator;

class Rules
{
    /** @var array        order of filterOptions to apply     */
    public $filterOrder = array();

    /** @var array        predefined filter filter set        */
    public $filterTypes = array();

    public $type = null;
    
    public $filter = array();
    // +----------------------------------------------------------------------+
    /**
     */
    public function __construct()
    {
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
            'sameEmpty'   => false,
        );

        // filters for various types of input.
        $this->filterTypes = array(
            'binary' => array(
                'noNull:FALSE | encoding:FALSE | mbConvert:FALSE | trim:FALSE '
            ),
            'text' => array(),
            'email' => array(
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
     * @param $method
     * @param $filters
     * @return Rules
     */
    public function __call( $method, $filters ) 
    {
        $rule = new self();
        $rule->type = $method;
        $typeFilter = $this->filterTypes[ $method ];
        $rule->filter = $this->mergeFilter( $typeFilter, $filters );
        return $rule;
    }
    
    public function getType() {
        return $this->type;
    }

    public function mergeFilter()
    {
        $args = func_get_args();
        $prepFilter = $this->filterOrder;
        foreach( $args as $filter ) 
        {
            if( is_string( $filter ) ) {
                $filter = $this->convertFilter( $filter );
            }
            if( empty( $filter ) ) continue;
            foreach( $filter as $key => $val ) {
                $prepFilter[ $key ] = $val;
            }
        }
        return $prepFilter;
    }
    /**
     * converts string filter to array. string in: 'rule1:parameter1|rule2:parameter2'
     *
     * @param string|array $filter
     * @return array
     */
    public function convertFilter( $filter )
    {
        if( !$filter ) return array();
        if( is_array( $filter ) ) return $filter;
        
        $filter_array = array();
        $rules = explode( '|', $filter );
        foreach( $rules as $rule ) 
        {
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
}