<?php
namespace WScore\Validator;

/** @method date() */
class Rules
{
    /** @var array        order of filterOptions to apply     */
    protected $filterOrder = array();

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
            'multiple'    => false,      // multiple value for DataIO.
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
            'sameWith'    => false,      // comparing with other field for DataIO. 
            'sameAs'      => false,
            'sameEmpty'   => false,
        );

        // filters for various types of input.
        $this->filterTypes = array(
            'binary'   => 'noNull:FALSE | encoding:FALSE | mbConvert:FALSE | trim:FALSE ',
            'text'     => '',
            'email'    => 'mbConvert:hankaku|sanitize:email|pattern:mail',
            // TODO: think of better regular date filter rules. 
            'date'     => 'multiple:date | mbConvert:hankaku | pattern:[0-9]{4}-[0-9]{2}-[0-9]{2}',
            'dateYM'   => 'multiple:YM | mbConvert:hankaku | pattern:[0-9]{4}-[0-9]{2}',
            'time'     => '',
            'datetime' => '',
            'tel'      => '',
            'fax'      => '',
        );
        
        // default filter is filterOrder. 
        $this->filter = $this->filterOrder;
    }

    public function getType() {
        return $this->type;
    }

    // +----------------------------------------------------------------------+
    /**
     * @param $type
     * @param $filters
     * @return Rules
     */
    public function ruleForType( $type, $filters )
    {
        $rule = new static();
        $rule->type = $type;
        $typeFilter = $this->filterTypes[ $type ];
        $rule->mergeFilter( $typeFilter );
        $rule->mergeFilter( $filters );
        return $rule;
    }

    /**
     * @param null|string|array $filters
     * @return Rules
     */
    public function email( $filters=null ) {
        return $this->ruleForType( 'email', $filters );
    }

    /**
     * @param string $method
     * @param mixed $args
     * @return Rules
     */
    public function __call( $method, $args ) {
        $filter = array_key_exists( 0, $args ) ? $args[0]: null;
        return $this->ruleForType( $method, $filter );
    }
    // +----------------------------------------------------------------------+
    //  tools for filters. 
    // +----------------------------------------------------------------------+
    /**
     * merges text/array filters into Rule object's filter. 
     * 
     * @param array $filter ,
     * @return array
     */
    public function mergeFilter( $filter )
    {
        if( is_string( $filter ) ) {
            $filter = $this->convertFilter( $filter );
        }
        if( empty( $filter ) ) return;
        foreach( $filter as $key => $val ) {
            $this->filter[ $key ] = $val;
        }
        return;
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