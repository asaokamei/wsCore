<?php
namespace WScore\DataMapper;

class CenaManager
{
    public $cena = 'Cena';
    
    // +----------------------------------------------------------------------+
    public function __construct()
    {
        
    }
    // +----------------------------------------------------------------------+
    //  utility methods. 
    // +----------------------------------------------------------------------+
    /**
     * returns cena-formatted name for form elements.
     *
     * @param string  $cenaId
     * @param string  $type
     * @param null    $name
     * @return string
     */
    public function getFormName( $cenaId, $type='prop', $name=null )
    {
        $cena = explode( '.', $cenaId );
        $formName = $this->cena . '[' . implode( '][', $cena ) . "][{$type}]";
        if( $name ) $formName .= "[{$name}]";
        return $formName;
    }
    // +----------------------------------------------------------------------+
}