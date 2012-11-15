<?php
namespace wsCore\Html;

class Selector_Hidden extends Selector
{
    /**
     * @param Form $form
     */
    public function __construct( $form )
    {
        parent::__construct( $form );
        $this->style = 'hidden';
    }
}