<?php
namespace wsModule\Templates\Pure;

class Filter_Date
{
    public static function dot( $v ) {
        return str_replace( '-', '.', $v );
    }

}