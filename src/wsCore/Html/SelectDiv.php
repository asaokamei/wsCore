<?php
namespace wsCore\Html;

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

    /**
     * same as Selector's $types.
     * @var array
     */
    protected static $types = array(
        'edit' => 'form',
        'new'  => 'form',
        'disp' => 'html',
        'name' => 'html',
        'raw'  => 'raw'
    );

    public function __construct( $name ) {
        $this->name = $name;
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
            if( isset( $values[$i] ) ) {
                $forms[] = $this->d_forms[$i]->popHtml( 'form', $values[$i] );
            }
            else {
                $forms[] = $this->d_forms[$i]->popHtml( 'form' );
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
            $value = $this->htmlFilter( $value );
        }
        return $value;
    }
}