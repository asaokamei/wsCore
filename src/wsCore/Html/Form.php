<?php
namespace wsCore\Html;
/*

$element->form(
    $element->input( text, user_name, taro-san ),
    $element->radio( gender, [ m=>male, f=>female ], m ),
    $element->select( ages, [10=>teen, 20=>twenties, 30=>over], 20 )
)->action( 'do.php' );

 */
class Form extends Tags
{
    /** @var null|string        overwrites name ex: name[1] */
    static $var_format = NULL;

    protected $style = NULL;
    
    protected $type = NULL;

    protected $name = NULL;

    protected $value = NULL;

    protected $items = array();

    public $multiple = FALSE;
    
    // +----------------------------------------------------------------------+
    /**
     * quick method to create input element.
     *
     * @param string      $type
     * @param string      $name
     * @param null|string $value
     * @param array       $attributes
     * @param Form        $form
     * @return Form|Tags
     */
    public function input( $type, $name, $value=NULL, $attributes=array(), $form=NULL ) 
    {
        if( !$form ) $form = clone $this;
        $form->style = $type;
        $form->setTagName_( 'input' );
        $form->setType( $type );
        $form->setName( $name );
        $form->setValue( $value );
        $form->applyAttributes( $attributes );
        return $form;
    }

    /**
     * creates textArea element.
     *
     * @param string $name
     * @param null|string $value
     * @param array $attributes
     * @return Form|Tags
     */
    public function textArea( $name, $value=NULL, $attributes=array() ) 
    {
        $form = clone $this;
        $form->style = 'textarea';
        $form->setTagName_( 'textarea' );
        $form->applyAttributes( $attributes );
        $form->setName( $name );
        $form->contents[0] = '';
        return $form->contain_( $value );
    }

    public function setContents_( $contents )
    {
        // for textarea special.
        if( $this->tagName == 'textarea' ) {
            if( is_array( $contents ) ) {
                $contents = implode( '', $contents ); // to string.
            }
            $contents = $this->safe_( $contents );
            $this->contents[0] .= $contents;
            return $this;
        }
        return parent::setContents_( $contents );
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
     * @return Form|Tags
     */
    public function select( $name, $items, $checked=NULL, $attributes=array() ) 
    {
        $form = clone $this;
        if( array_key_exists( 'multiple', $attributes ) ) $form->multiple = TRUE;
        $form->style = 'select';
        $form->setTagName_( 'select' );
        $form->setName( $name );
        $form->items = $items;
        $form->applyAttributes( $attributes );
        $form->makeOptions( $form, $items, $checked );
        return $form;
    }

    /**
     * makes option list for Select box.
     *
     * @param Form $select
     * @param array $items
     * @param array|string $checked
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
            $option = $this::_()->option( $label )->value( $value );
            if( in_array( $value, $checked ) ) $option->checked();
            if( isset( $item[2] ) ) 
            {
                $group = $item[2];
                if( $prev_group != $group ) {
                    $optGroup = $this::_()->optgroup()->label( $group );
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
     * @return Form|Tags
     */
    public function radio( $name, $value, $attributes=array() ) 
    {
        $form = $this->input( 'radio', $name, $value, $attributes );
        $form->style = 'radio';
        return $form;
    }

    /**
     * make single checkbox button.
     * 
     * @param string $name
     * @param string $value
     * @param array $attributes
     * @return Form|Tags
     */
    public function check( $name, $value, $attributes=array() ) 
    {
        $form = clone $this;
        $form->multiple = TRUE;
        $form = $this->input( 'checkbox', $name, $value, $attributes, $form );
        $form->style = 'checkbox';
        return $form;
    }

    /**
     * make single radio button inside label tag with $label as description.
     * @param string $name
     * @param       $value
     * @param       $label
     * @param array $attributes
     * @return Form|Tags
     */
    public function radioLabel( $name, $value, $label, $attributes=array() ) {
        return $this::_()->label( $this->radio( $name, $value, $attributes ) . $label );
    }

    /**
     * make single checkbox button inside label tag with $label as description.
     * @param string $name
     * @param       $value
     * @param       $label
     * @param array $attributes
     * @return Form|Tags
     */
    public function checkLabel( $name, $value, $label, $attributes=array() ) {
        return $this::_()->label( $this->check( $name, $value, $attributes ) . $label );
    }

    /**
     * make list of radio button div > nl > li > label > input:type=radio.
     *
     * @param string $name
     * @param array $items
     * @param array $checked
     * @param array $attributes
     * @return Form|Tags
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
     * @return Form|Tags
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
     * @return Form|Tags
     */
    public function doBox( $style, $name, $items, $checked=array(), $attributes=array() )
    {
        if( $checked && !is_array( $checked ) ) $checked = array( $checked ); 
        $list = $this::_()->nl();
        $div = $this::_()->div( $list )->_class( 'formListBox' );
        foreach( $items as $item ) {
            $value = $item[0];
            $label = $item[1];
            /** @var $check Form */
            $check = $this()->$style( $name, $value, $attributes );
            if( in_array( $value, $checked ) ) $check->checked();
            $list->contain_(
                $this::_()->li( $this::_()->label( $check . $label )
                ));
        }
        return $div;
    }

    public function listBox( $items, $name=NULL )
    {
        $list = $this::_()->nl();
        $div = $this::_()->div( $list )->_class( 'formListBox' . $name );
        foreach( $items as $item ) {
            $list->contain_( $this::_()->li( $item ) );
        }
        return $div;
    }
    // +----------------------------------------------------------------------+
    /**
     * @param  array $attribute
     * @return Form|Tags
     */
    public function applyAttributes( $attribute )
    {
        if( empty( $attribute ) ) return $this;
        foreach( $attribute as $name => $value ) {
            $method = '_' . $name;
            $this->$method( $value );
        }
        return $this;
    }
    /**
     * set up ime mode in style.
     *
     * @param $ime
     * @return Form|Tags
     */
    public function _ime( $ime ) {
        static $ime_style;
        if( !isset( $ime_style ) ) {
            $ime_style = array(
                'ON'  => 'ime-mode:active',
                'OFF' => 'ime-mode:inactive',
                'I1'  => 'istyle:1',
                'I2'  => 'istyle:2',
                'I3'  => 'istyle:3',
                'I4'  => 'istyle:4',
            );
        }
        if( isset( $ime_style[ strtoupper( $ime ) ] ) ) {
            $this->_style( $ime_style[ strtoupper( $ime ) ] );
        }
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
     * @return Form|Tags
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
     * @return Form|Tags
     */
    public function setId( $id=NULL ) {
        return $this;
    }

    /**
     * set name attribute. name may be changed (name[1]) for checkbox etc.
     *
     * @param string $name
     * @return Form|Tags
     */
    public function setName( $name ) {
        $this->name = $name;
        if( $this->multiple ) $name .= '[]';
        $this->setAttribute_( 'name', $name );
        return $this;
    }
    // +----------------------------------------------------------------------+
}