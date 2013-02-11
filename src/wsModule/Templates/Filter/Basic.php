<?php
namespace wsModule\Templates\Pure;

class Filter_Basic
{
    public static function h( $v ) {
        return htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' );
    }
    
    public static function nl2br( $v ) {
        return nl2br( $v );
    }
}