<?php
namespace wsCore\Html;

class Hidden extends Selector
{
    /**
     * @param Form $form
     */
    public function __construct( $form )
    {
        $this->form = $form;
        $this->style = 'hidden';
    }
}