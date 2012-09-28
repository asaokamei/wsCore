<?php
namespace wsCore\Html;

class Selector_selYear extends Selector
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
        $start_y               = $this->arrGet( $option, 'start_y', date( 'Y' ) - 10 );
        $end_y                 = $this->arrGet( $option, 'end_y',   date( 'Y' ) + 1 );
        for( $year = $start_y; $year <= $end_y; $year ++ ) {
            $this->item_data[] = array(
                sprintf( '%4d', $year ),
                sprintf( '%4d', $year )
            );
        }
    }
    public function makeHtml( $value ) {
        $value = parent::makeHtml( $value );
        if( $value ) $value .= '年';
        return $value;
    }
}