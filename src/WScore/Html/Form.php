<?php
namespace WScore\Html;
/*

$element->form(
    $element->input( text, user_name, taro-san ),
    $element->radio( gender, [ m=>male, f=>female ], m ),
    $element->select( ages, [10=>teen, 20=>twenties, 30=>over], 20 )
)->action( 'do.php' );

 */
/**
 * @method \WScore\Html\Form list()
 * @method \WScore\Html\Form datalist()
 */
class Form extends Tags
{
    /** @var null|string        overwrites name ex: name[1] */
    static $var_format = null;

    public $style = null;

    public $type = null;

    public $value = null;

    public $items = array();

    public $multiple = false;
    
    // +----------------------------------------------------------------------+
    /**
     * quick method to create input element.
     *
     * @param string      $type
     * @param string      $name
     * @param null|string $value
     * @param array       $attributes
     * @return Form|Tags
     */
    public function input( $type, $name, $value=null, $attributes=array() ) 
    {
        $form = clone $this;
        $form->style = $type;
        $form->_setTagName( 'input' );
        $form->setType( $type );
        $form->setName( $name );
        $form->setValue( $value );
        $form->applyAttributes( $attributes );
        if( array_key_exists( 'multiple', $attributes ) ) $form->multipleName();
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
    public function textArea( $name, $value=null, $attributes=array() ) 
    {
        $form = clone $this;
        $form->style = 'textarea';
        $form->_setTagName( 'textarea' );
        $form->applyAttributes( $attributes );
        $form->setName( $name );
        $form->contents[0] = '';
        return $form->_contain( $value );
    }

    public function _setContents( $contents )
    {
        // for textarea special.
        if( $this->tagName == 'textarea' ) {
            if( is_array( $contents ) ) {
                $contents = implode( '', $contents ); // to string.
            }
            $contents = $this->_safe( $contents );
            $this->contents[0] .= $contents;
            return $this;
        }
        return parent::_setContents( $contents );
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
    public function select( $name, $items, $checked=array(), $attributes=array() )
    {
        $form = clone $this;
        $form->style = 'select';
        $form->_setTagName( 'select' );
        $form->setName( $name );
        $form->items = $items;
        $form->applyAttributes( $attributes );
        $form->makeOptions( $form, $items, $checked );
        if( array_key_exists( 'multiple', $attributes ) ) $form->multipleName();
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
        $groupList = array();
        foreach( $items as $item ) 
        {
            $value = $item[0];
            $label = $item[1];
            $option = $this::_new()->option( $label )->value( $value );
            if( in_array( $value, $checked ) ) {
                /** @noinspection PhpUndefinedMethodInspection */
                $option->selected( true );
            }
            if( isset( $item[2] ) ) 
            {
                $group = $item[2];
                if( array_key_exists( $group, $groupList ) ) {
                    $optGroup = $groupList[ $group ];
                }
                else {
                    $optGroup = $this::_new()->optgroup()->label( $group );
                    $select->_contain( $optGroup );
                    $groupList[ $group ] = $optGroup;
                }
                $optGroup->_contain( $option );
            }
            else {
                $select->_contain( $option );
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
        $form = $this->input( 'checkbox', $name, $value, $attributes );
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
        return $this::_new()->label( $this->radio( $name, $value, $attributes ), $label );
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
        return $this::_new()->label( $this->check( $name, $value, $attributes ), $label );
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
        $div = $this->doBox( 'check', $name, $items, $checked, $attributes );
        $div->multipleName();
        return $div;
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
        if( !is_array( $checked ) ) $checked = array( $checked );
        $list = $this::_new()->nl();
        /** @var $div Form */
        $div = $this::_new()->div( $list )->class_( 'formListBox' );
        foreach( $items as $item ) {
            $value = $item[0];
            $label = $item[1];
            /** @var $check Form */
            $check = $this()->$style( $name, $value, $attributes );
            if( in_array( $value, $checked ) ) $check->checked( true );
            $list->_contain(
                $this::_new()->li( $this::_new()->label( $check, $label )
                ));
        }
        return $div;
    }

    public function lists( $items, $name=null ) {
        $list = $this::_new()->nl()->class_( 'formListBox' . $name );
        foreach( $items as $item ) {
            $list->_contain( $this::_new()->li( $item ) );
        }
        return $list;
    }
    public function listBox( $items, $name=null )
    {
        $list = $this::_new()->nl();
        $div = $this::_new()->div( $list )->class_( 'formListBox' . $name );
        foreach( $items as $item ) {
            $list->_contain( $this::_new()->li( $item ) );
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
            $this->style_( $ime_style[ strtoupper( $ime ) ] );
        }
        return $this;
    }

    public function setValue( $value ) {
        $this->value = $value;
        $this->_setAttribute( 'value', $value );
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
        $this->_setAttribute( 'type', $type );
        return $this;
    }
    /**
     * set id attribute; id is generated from name if not set.
     *
     * @param string|null $id
     * @return Form|Tags
     */
    public function setId( $id=null ) {
        if( !$id ) {
            $id = array_key_exists( 'name', $this->attributes ) ? $this->attributes[ 'name' ] : false;
            if( $id === false ) return $this; // do not set id for tags without name attribute.
            $id = str_replace( array( '[', ']' ), '_', $id );
            if( in_array( $this->type, array( 'checkbox', 'radio' ) ) && isset( $this->value )) {
                $id .= '_' . $this->value;
            }
        }
        $this->_setAttribute( 'id', $id );
        return $this;
    }

    /**
     * set name attribute. name may be changed (name[1]) for checkbox etc.
     *
     * @param string $name
     * @return Form|Tags
     */
    public function setName( $name ) {
        $this->_setAttribute( 'name', $name );
        return $this;
    }

    /**
     * makes the form object to array style name (i.e. name="varName[]").
     * @return Form
     */
    public function multipleName()
    {
        $addMultiple = function( $form ) {
            /** @var $form Form */
            if( isset( $form->attributes[ 'name' ] ) ) { $form->attributes[ 'name' ].= '[]'; }
        };
        /** @var $div Form */
        $this->walk( $addMultiple );
        return $this;
    }

    /**
     * @return Form
     */
    public function walkSetId()
    {
        $addId = function( $form ) {
            /** @var $form Form */
            $form->setId();
        };
        $this->walk( $addId );
        return $this;
    }
    // +----------------------------------------------------------------------+
}