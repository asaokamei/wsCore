<?php
namespace WScore\Html;

class Selector_Textarea extends Selector
{
    /**
     * @param Form $form
     */
    public function __construct( $form )
    {
        parent::__construct( $form );
        $this->style = 'textarea';
        $this->htmlFilter = function( $v ) {
            $v = htmlentities( $v, ENT_QUOTES, 'UTF-8');
            $v = nl2br( $v );
            return $v;
        };
    }
}