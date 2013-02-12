<?php
namespace wsModule\Templates;

class Filter_Date
{
    public function dot( $v ) {
        return str_replace( '-', '.', $v );
    }

}