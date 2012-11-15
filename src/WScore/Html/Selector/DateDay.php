<?php
namespace WScore\Html;

class Selector_DateDay extends Selector
{
    public function __construct( $form )
    {
        parent::__construct( $form );
        $this->style  = 'SELECT';

    }
    public function set( $name, $option=array(), $htmlFilter=NULL )
    {
        $this->name            = $name;
        $this->add_head_option = $this->arrGet( $option, 'add_head', '' );
        for( $day = 1; $day <= 31; $day ++ ) {
            $this->item_data[] = array(
                sprintf( '%2d', $day ),
                sprintf( '%2d', $day )
            );
        }
    }
    public function makeHtml( $value ) {
        $value = parent::makeHtml( $value );
        if( $value ) $value .= '日';
        return $value;
    }
}