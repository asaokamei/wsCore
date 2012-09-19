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
    public $style     = '';
    public $name      = '';
    public $item_data = array();
    public $default_items;
    public $err_msg_empty;
    public $add_head_option;
    public $attributes = array();

    public function __construct( $name ) {}

    public function popHtml( $type, $values, $err_msg ) {}

    public function show( $type, $value ) {}

    public function setIME( $value ) {}

    public function addClass( $class ) {}

    public function __set( $name, $value ) {
        $this->attributes[ $name ] = $value;
        return $this;
    }
}