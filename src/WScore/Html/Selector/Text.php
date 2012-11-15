<?php
namespace WScore\Html;

class Selector_Text extends Selector
{
    /**
     * @param Form $form
     */
    public function __construct( $form )
    {
        parent::__construct( $form );
        $this->style = 'text';
    }
}