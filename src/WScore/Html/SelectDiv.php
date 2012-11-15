<?php
namespace WScore\Html;

class SelectDiv
{
    /** @var string                   value is divided (a-b-c) */
    public $divider = '-';

    /** @var string                   form is divided (A-B-C)  */
    public $dividerForm = '-';

    /** @var int                      number of divisions      */
    public $num_div = 0;

    /** @var array                    each form                */
    public $d_forms = array();

    /** @var NULL|String              default value            */
    public $default_items = NULL;

    /** @var \Closure */
    public $htmlFilter = NULL;

    /** @var Form */
    protected $form; 
    
    /** @var string  */
    public $name;
    /**
     * same as Selector's $types.
     * @var array
     */
    protected static $types = array(
        'form' => 'form',
        'edit' => 'form',
        'new'  => 'form',
        'html' => 'html',
        'disp' => 'html',
        'name' => 'html',
        'raw'  => 'raw'
    );

    public function __construct( $form ) {
        $this->form = clone $form;
    }

    /**
     * set up Selector. make sure to overload this function.
     *
     * @param string        $name
     * @param array         $option
     * @param callable|null $htmlFilter
     */
    public function set( $name, $option=array(), $htmlFilter=NULL )
    {
    }
    public function arrGet( $arr, $key, $default=NULL ) {
        if( array_key_exists( $key, $arr ) ) {
            return $arr[ $key ];
        }
        return $default;
    }
    public function popHtml( $type, $value )
    {
        if( !$value && $this->default_items ) $value = $this->default_items;
        $type = \strtolower( $type );
        $type = ( isset( static::$types[ $type ] ) ) ? ucwords( static::$types[ $type ] ) : 'Html';
        $method = 'make' . $type;
        return $this->$method( $value );
    }
    /**
     * makes RAW type of a value.
     * returns as is for single value, returns as 'div > nl > li' for arrays.
     *
     * @param $value
     * @return mixed
     */
    public function makeRaw( $value ) {
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
        $values = $this->makeName( $value );
        return $values;
    }
    /**
     * make FORM type of value.
     * create HTML Form element based on style.
     *
     * @param $value
     * @return mixed
     */
    public function makeForm( $value )
    {
        $values = $this->splitValue( $value );
        $forms  = array();
        for( $i = 0; $i < $this->num_div ; $i++ ) {
            /** @var $d_form Selector */
            $d_form = $this->d_forms[$i];
            if( isset( $values[$i] ) ) {
                $forms[] = $d_form->popHtml( 'form', $values[$i] );
            }
            else {
                $forms[] = $d_form->popHtml( 'form' );
            }
        }
        return implode( $this->dividerForm, $forms );
    }

    /**
     * split value (a-b-c) into an array( [a,b,c] ).
     * overwrite this method for each special class.
     *
     * @param $value
     * @return array
     */
    public function splitValue( $value ) {
        return explode( $this->divider, $value );
    }

    /**
     * make final display name (ex: a-b-c -> c, a/b).
     * overwrite this method for each special class.
     *
     * @param $value
     * @return mixed
     */
    public function makeName( $value ) {
        if( isset( $this->htmlFilter ) ) {
            $value = call_user_func( $this->htmlFilter, $value );
        }
        return $value;
    }
}