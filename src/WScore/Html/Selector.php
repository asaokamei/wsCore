<?php
namespace WScore\Html;

/*

Selector( $style, $name, $opt1, $opt2, $ime );

class htmlText extends Selector {
    function __construct( $name, $width, $limit, $ime ) {
        $this->style = 'text';
        $this->name  = $name;
        $this->width = $width;
        $this->maxlength = $limit;
        $this->setIME( $ime );
    }
}

class sel_active_flag extends Selector {
    function __construct( $name, $opt1, $opt2, $ime ) {
        // code should work as is pretty much.
    }
}

class selYear ...

class selDateDs ...

*/
class Selector
{
    /**
     * html type: select, textarea, radio, check, and others (text, hidden, date, etc.)
     * @var string
     *
     */
    public $style           = '';
    public $name            = '';
    public $item_data       = array();
    public $default_items   = '';
    public $err_msg_empty   = '*invalid*';
    public $add_head_option = '';
    public $attributes      = array( 'class' => 'FormSelector' );

    /** @var callable|null */
    public $htmlFilter      = NULL;

    /** @var Form */
    protected $form;
    protected static $types = array(
        'form' => 'form',
        'edit' => 'form',
        'new'  => 'form',
        'html' => 'html',
        'disp' => 'html',
        'name' => 'html',
        'raw'  => 'raw'
    );
    protected static $formStyle = array(
        'textarea'    => 'textarea',
        'select'      => 'select',
        'mult_select' => 'select',
        'check'       => 'check',
        'check_hor'   => 'check',
        'check_ver'   => 'check',
        'radio'       => 'radio',
        'radio_hor'   => 'radio',
        'radio_ver'   => 'radio',
    );
    public static $encoding = 'UTF-8';

    // +----------------------------------------------------------------------+
    /**
     * @param Form $form
     * @DimInjection Fresh \WScore\Html\Form
     */
    public function __construct( $form )
    {
        $this->form = $form;
        $this->htmlFilter = function( $v ) {
            return htmlentities( $v, ENT_QUOTES, 'UTF-8');
        };
    }

    /**
     * set up Selector. make sure to overload this function. 
     *
     * @param string      $name
     * @param array $option
     * @param null|\closure $htmlFilter
     */
    public function set( $name, $option=array(), $htmlFilter=NULL )
    {
        $this->name  = $name;
        // setup filter for html safe value.
        if( $htmlFilter ) {
            $this->htmlFilter = $htmlFilter;
        }
        $this->attributes = array_merge( $this->attributes, $option );
    }

    /**
     * get instances of Selector for various styles in Selector_*. 
     * 
     * @param string   $style
     * @param string   $name
     * @param string   $option
     * @param \Closure $htmlFilter
     * @return Selector
     * @throws \RuntimeException
     */
    public function getInstance( $style, $name, $option=NULL, $htmlFilter=NULL )
    {
        if( class_exists( $class = '\WScore\Html\Selector_' . ucwords( $style ) ) ) {
            $class = '\WScore\Html\Selector_' . ucwords( $style );
        }        
        elseif( class_exists( $style ) ) {
            $class = $style;
        }
        else {
            throw new \RuntimeException( "$style not found." );
        }
        /** @var $selector Selector */
        $option   = $this->prepareFilter( $option );
        $selector = new $class( $this->form );
        $selector->set( $name, $option, $htmlFilter );
        return $selector;
    }
    // +----------------------------------------------------------------------+
    /**
     * pops HTML/FORM/RAW ($type) of the Selector for a given $value.
     *
     * @param $type
     * @param null $value
     * @return mixed
     */
    public function popHtml( $type, $value=NULL )
    {
        $type = \strtolower( $type );
        $type = ( isset( static::$types[ $type ] ) ) ? ucwords( static::$types[ $type ] ) : 'Html';
        $method = 'make' . $type;
        return $this->$method( $value );
    }

    /**
     * @param $type
     * @param $value
     * @return mixed
     */
    public function show( $type, $value=NULL ) {
        return $this->popHtml( $type, $value );
    }

    /**
     * makes RAW type of a value.
     * returns as is for single value, returns as 'div > nl > li' for arrays.
     *
     * @param $value
     * @return mixed
     */
    public function makeRaw( $value ) {
        if( is_array( $value ) ) {
            return $this->form->listBox( $value );
        }
        return $value;
    }
    // +----------------------------------------------------------------------+
    /**
     * makes HTML safe value.
     *
     * @param string|array $value
     * @return string|void
     */
    public function makeHtml( $value )
    {
        if( !empty( $this->item_data ) ) {
            // match with items. assumed values are safe.
            $value = $this->makeHtmlItems( $value );
        }
        elseif( is_array( $value ) ) {
            // input is an array. make all safely encode.
            array_walk( $value, $this->htmlFilter );
        }
        else {
            // encode input value for safety.
            $value = call_user_func( $this->htmlFilter, $value );
        }
        return $this->makeRaw( $value );
    }

    /**
     * returns itemized value as an array.
     * replaces the value with err_msg_empty if value fails to match with item_data.
     *
     * @param $value
     * @return array
     */
    public function makeHtmlItems( $value )
    {
        if( !is_array( $value ) ) $value = array( $value );
        foreach( $value as $key => &$val ) {
            if( $string = $this->findValueFromItems( $val ) ) {
                $value[ $key ] = $string;
            }
            else {
                $value[ $key ] = $this->err_msg_empty;
            }
        }
        return $value;
    }

    /**
     * finds a value for a given single value from itemized data.
     *
     * @param $value
     * @return bool
     */
    public function findValueFromItems( &$value )
    {
        foreach( $this->item_data as $item ) {
            if( $value == $item[0] ) {
                return $item[1];
            }
        }
        return FALSE;
    }
    // +----------------------------------------------------------------------+
    /**
     * make FORM type of value.
     * create HTML Form element based on style.
     *
     * @param $value
     * @return mixed
     */
    public function makeForm( $value )
    {
        if( is_null( $value ) ) { // use default value if value is not set.
            $value = $this->default_items;
        }
        if( $this->add_head_option && !empty( $this->item_data ) ) {
            $this->item_data = array_merge(
                array( array( '', $this->add_head_option ) ), // first item with empty value.
                $this->item_data
            );
        }
        $style = strtolower( $this->style );
        $formStyle = ( isset( static::$formStyle[ $style ] ) ) ? static::$formStyle[ $style ]: 'input';
        $method = 'form' . ucwords( $formStyle ); // select, textarea, radioBox, checkBox
        return $this->$method( $value );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formInput( $value ) {
        return $this->form->input( $this->style, $this->name, $value, $this->attributes );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formTextarea( $value ) {
        return $this->form->textArea( $this->name, $value, $this->attributes );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formSelect( $value ) {
        $form = $this->form;
        if( $this->style == 'mult_select' ) $form->multiple = TRUE;
        return $form->select( $this->name, $this->item_data, $value, $this->attributes );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formRadio( $value ) {
        return $this->form->radioBox( $this->name, $this->item_data, $value, $this->attributes );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formCheck( $value ) {
        return $this->form->checkBox( $this->name, $this->item_data, $value, $this->attributes );
    }
    // +----------------------------------------------------------------------+
    /**
     * @param $class
     */
    public function addClass( $class ) {
        $this->attributes[ 'class' ] .= " $class";
    }
    
    /**
     * prepares filter if it is in string; 'rule1:parameter1|rule2:parameter2'
     * This is copied from Validator. DRY!
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

    /**
     * @param array $arr
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function arrGet( $arr, $key, $default=NULL ) {
        if( array_key_exists( $key, $arr ) ) {
            return $arr[ $key ];
        }
        return $default;
    }
    // +----------------------------------------------------------------------+
}