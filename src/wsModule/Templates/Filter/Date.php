<?php
namespace wsModule\Templates;

class Filter_Date
{
    public static function dot( $v ) {
        return str_replace( '-', '.', $v );
    }

}