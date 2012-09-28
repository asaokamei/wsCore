<?php
namespace wsCore\Html;

class selYear extends Selector
{
    public function __construct( $name='year', $start_y=NULL, $end_y=NULL )
    {
        $this->name   = $name;
        $this->style  = 'SELECT';

        if( !$start_y ) $start_y = date( 'Y' ) - 10;
        if( !$end_y   ) $end_y   = date( 'Y' ) + 1;
        $this->default_items   = date( 'Y' );
        $this->add_head_option = "";
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