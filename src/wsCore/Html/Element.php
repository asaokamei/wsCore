<?php
namespace wsCore\Html;
/*

$element->form(
    $element->input( text, user_name, taro-san ),
    $element->radio( gender, [ m=>male, f=>female ], m ),
    $element->select( ages, [10=>teen, 20=>twenties, 30=>over], 20 )
)->action( 'do.php' );

 */
class Element extends Tags
{
    /** @var null|string        overwrites name ex: name[1] */
    static $var_format = NULL;

    protected $style = NULL;
    
    protected $type = NULL;

    protected $name = NULL;

    protected $value = NULL;

    protected $items = array();

    protected $multiple = FALSE;
    
    // +----------------------------------------------------------------------+
    /**
     * quick method to create input element.
     *
     * @param string $type
     * @param string $name
     * @param null|string $value
     * @param array $attributes
     * @return \wsCore\Html\Element
     */
    public function input( $type, $name, $value=NULL, $attributes=array() ) 
    {
        $this->style = $type;
        $this->setTagName_( 'input' );
        $this->setType( $type );
        $this->setName( $name );
        $this->setValue( $value );
        $this->applyAttributes( $attributes );
        return $this;
    }

    /**
     * creates textArea element.
     *
     * @param string $name
     * @param null|string $value
     * @param array $attributes
     * @return \wsCore\Html\Element
     */
    public function textArea( $name, $value=NULL, $attributes=array() ) 
    {
        $this->style = 'textarea';
        $this->setTagName_( 'textarea' );
        $this->applyAttributes( $attributes );
        $this->setName( $name );
        return $this->contain_( $value );
    }

    // +----------------------------------------------------------------------+
    /**
     * make select list.
     * 
     * @param string $name
     * @param array $items
     * @param array $checked
     * @param array $attributes
     * @internal param array $value
     * @return \wsCore\Html\Element
     */
    public function select( $name, $items, $checked=NULL, $attributes=array() ) 
    {
        if( array_key_exists( 'multiple', $attributes ) ) $this->multiple = TRUE;
        $this->style = 'select';
        $this->setTagName_( 'select' );
        $this->setName( $name );
        $this->items = $items;
        $this->applyAttributes( $attributes );
        $this-> makeOptions( $this, $items, $checked );
        return $this;
    }

    /**
     * makes option list for Select box.
     *
     * @param $select
     * @param $items
     * @param $checked
     * @return void
     */
    public function makeOptions( $select, $items, $checked ) 
    {
        if( $checked && !is_array( $checked ) ) $checked = array( $checked );
        $prev_group = NULL;
        foreach( $items as $item ) 
        {
            $value = $item[0];
            $label = $item[1];
            $option = $this()->option( $label )->value( $value );
            if( in_array( $value, $checked ) ) $option->checked();
            if( isset( $item[2] ) ) 
            {
                $group = $item[2];
                if( $prev_group != $group ) {
                    $optGroup = $this()->optgroup()->label( $group );
                    $select->contain_( $optGroup );
                    $prev_group = $group;
                }
                if( isset( $optGroup ) ) $optGroup->contain_( $option );
            }
            else {
                $select->contain_( $option );
            }
        }
    }
    // +----------------------------------------------------------------------+
    /**
     * make single radio button element. 
     * 
     * @param string $name
     * @param string $value
     * @param array $attributes
     * @return Element
     */
    public function radio( $name, $value, $attributes=array() ) 
    {
        $this->input( 'radio', $name, $value, $attributes );
        $this->style = 'radio';
        return $this;
    }

    /**
     * make single checkbox button. 
     * 
     * @param string $name
     * @param string $value
     * @param array $attributes
     * @return Element
     */
    public function check( $name, $value, $attributes=array() ) 
    {
        $this->multiple = TRUE;
        $this->input( 'checkbox', $name, $value, $attributes );
        $this->style = 'checkbox';
        return $this;
    }

    /**
     * make single radio button inside label tag with $label as description.
     * @param string $name
     * @param       $value
     * @param       $label
     * @param array $attributes
     * @return mixed
     */
    public function radioLabel( $name, $value, $label, $attributes=array() ) {
        return $this()->label( $this->radio( $name, $value, $attributes ) . $label );
    }

    /**
     * make single checkbox button inside label tag with $label as description.
     * @param string $name
     * @param       $value
     * @param       $label
     * @param array $attributes
     * @return mixed
     */
    public function checkLabel( $name, $value, $label, $attributes=array() ) {
        return $this()->label( $this->check( $name, $value, $attributes ) . $label );
    }

    /**
     * make list of radio button div > nl > li > label > input:type=radio.
     *
     * @param string $name
     * @param array $items
     * @param array $checked
     * @param array $attributes
     * @return mixed
     */
    public function radioBox( $name, $items, $checked=array(), $attributes=array() ) {
        return $this->doBox( 'radio', $name, $items, $checked, $attributes );
    }

    /**
     * make list of check button div > nl > li > label > input:type=check.
     *
     * @param string $name
     * @param array $items
     * @param array $checked
     * @param array $attributes
     * @return mixed
     */
    public function checkBox( $name, $items, $checked=array(), $attributes=array() ) {
        return $this->doBox( 'check', $name, $items, $checked, $attributes );
    }

    /**
     * the body routine for radioBox and checkBox. 
     * 
     * @param string $style
     * @param string $name
     * @param array $items
     * @param array $checked
     * @param array $attributes
     * @return mixed
     */
    public function doBox( $style, $name, $items, $checked=array(), $attributes=array() )
    {
        if( $checked && !is_array( $checked ) ) $checked = array( $checked ); 
        $list = $this()->nl();
        $div = $this()->div( $list )->class( 'formListBox' );
        foreach( $items as $item ) {
            $value = $item[0];
            $label = $item[1];
            $check = $this()->$style( $name, $value, $attributes );
            if( in_array( $value, $checked ) ) $check->checked();
            $list->contain_(
                $this()->li( $this()->label( $check . $label )
                ));
        }
        return $div;
    }

    // +----------------------------------------------------------------------+
    /**
     * @param  array $attribute
     * @return Element
     */
    public function applyAttributes( $attribute )
    {
        if( empty( $attribute ) ) return $this;
        foreach( $attribute as $name => $value ) {
            $this->setAttribute_( $name, $value );
        }
        return $this;
    }
    /**
     * set up ime mode in style.
     *
     * @param $ime
     * @return \wsCore\Html\Element
     */
    public function ime( $ime ) {
        return $this;
    }

    public function setValue( $value ) {
        $this->value = $value;
        $this->setAttribute_( 'value', $value );
        return $this;
    }
    /**
     * set type for input.
     * 
     * @param  string $type
     * @return Element
     */
    public function setType( $type )
    {
        $this->type = $type;
        $this->setAttribute_( 'type', $type );
        return $this;
    }
    /**
     * set id attribute; id is generated from name.
     *
     * @param string|null $id
     * @return \wsCore\Html\Element
     */
    public function setId( $id=NULL ) {
        return $this;
    }

    /**
     * set name attribute. name may be changed (name[1]) for checkbox etc.
     *
     * @param string $name
     * @return \wsCore\Html\Element
     */
    public function setName( $name ) {
        $this->name = $name;
        if( $this->multiple ) $name .= '[]';
        $this->setAttribute_( 'name', $name );
        return $this;
    }

    /**
     * returns Selector based on $type.
     * $types are:
     *   - NAME      : returns value (html encoded for safety).
     *   - EDIT, NEW : returns HTML form.
     *   - RAW:      : returns value without encoded.
     *
     * @param string $type
     * @param string $value
     * @return \wsCore\Html\Element
     */
    public function show( $type='NAME', $value='' ) {
        return $this;
    }

    /**
     * returns $value.
     * for Select, Radio, and Checkbox, returns label name from $value.
     */
    public function makeName() {
        return $this;
    }
    // +----------------------------------------------------------------------+
}