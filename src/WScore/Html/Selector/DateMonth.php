<?php
namespace WScore\Html;

class Selector_DateMonth extends Selector
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
        for( $month = 1; $month <= 12; $month ++ ) {
            $this->item_data[] = array(
                sprintf( '%2d', $month ),
                sprintf( '%2d', $month )
            );
        }
    }
    public function makeHtml( $value ) {
        $value = parent::makeHtml( $value );
        if( $value ) $value .= '月';
        return $value;
    }
}