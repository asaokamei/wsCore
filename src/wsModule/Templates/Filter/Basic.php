<?php
namespace wsModule\Templates;

class Filter_Basic
{
    public function h( $v ) {
        return htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' );
    }
    
    public function nl2br( $v ) {
        return nl2br( $v );
    }
}