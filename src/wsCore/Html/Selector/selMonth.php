<?php
namespace wsCore\Html;

class Selector_selMonth extends Selector
{
    public function __construct( $form, $name='year', $start_y=NULL, $end_y=NULL )
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