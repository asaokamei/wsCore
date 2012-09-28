<?php
namespace wsCore\Html;

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
        'edit' => 'form',
        'new'  => 'form',
        'disp' => 'html',
        'name' => 'html',
        'raw'  => 'raw'
    );
    protected static $formStyle = array(
        'textArea'    => 'textArea',
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
     */
    public function __construct( $form )
    {
        $this->form = $form;
    }

    /**
     * @param string      $style
     * @param string      $name
     * @param null|string $option
     * @param null|\closure $htmlFilter
     */
    public function set( $style, $name, $option=NULL, $htmlFilter=NULL )
    {
        $this->style = $style;
        $this->name  = $name;
        // setup filter for html safe value.
        if( $htmlFilter ) {
            $this->htmlFilter = $htmlFilter;
        }
        elseif( $this->style == 'textarea' ) {
            $this->htmlFilter = function( &$v ) {
                $v = htmlentities( $v, ENT_QUOTES, 'UTF-8');
                $v = nl2br( $v );
            };
        }
        else {
            $this->htmlFilter = function( &$v ) {
                $v = htmlentities( $v, ENT_QUOTES, 'UTF-8');
            };
        }
    }
    public function getInstance( $style, $name, $option )
    {
        $class = 'Selector_' . ucwords( $style );
        /** @var $selector Selector */
        $selector = new $class( $this->form );
        $selector->set( $style, $name, $option );
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
        $type = \strtoupper( $type );
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
            return $this->form()->listBox( $value );
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
            $this->htmlFilter( $value );
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
            if( $this->findValueFromItems( $val ) ) {
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
                $value = $item[1];
                return TRUE;
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
        if( !is_null( $value ) ) { // use default value if value is not set.
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
        $method = 'make' . $formStyle; // select, textarea, radioBox, checkBox
        return $this->$method( $value );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formInput( $value ) {
        return $this->form()->input( $this->style, $this->name, $value, $this->attributes );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formTextarea( $value ) {
        return $this->form()->textArea( $this->name, $value, $this->attributes );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formSelect( $value ) {
        $form = $this->form();
        if( $this->style == 'mult_select' ) $form->multiple = TRUE;
        return $form->select( $this->name, $this->item_data, $value, $this->attributes );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formRadio( $value ) {
        return $this->form()->radioBox( $this->name, $this->item_data, $value, $this->attributes );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formCheck( $value ) {
        return $this->form()->checkBox( $this->name, $this->item_data, $value, $this->attributes );
    }
    // +----------------------------------------------------------------------+
    /**
     * @param $class
     */
    public function addClass( $class ) {
        $this->attributes[ 'class' ] .= " $class";
    }
    // +----------------------------------------------------------------------+
}