<?php
namespace wsCore\Html;

class dateYMD extends SelectDiv
{
    public function __construct( $name='date', $start_y=NULL, $end_y=NULL )
    {
        $this->name = $name;
        $this->implode_with_div = FALSE;
        $this->divider = '-';
        $this->num_div = 3;
        $this->d_forms[] = new selYear(  "{$this->name}_y", $start_y, $end_y );
        $this->d_forms[] = new selMonth( "{$this->name}_m" );
        $this->d_forms[] = new selDay(   "{$this->name}_d" );

        // shows date like 2012/01/23.
        $this->htmlFilter = function( $val ) {
            return str_replace( '-', '/', $val );
        };
    }
}