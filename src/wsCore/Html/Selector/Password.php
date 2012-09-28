<?php
namespace wsCore\Html;

class Password extends Selector
{
    /**
     * @param Form $form
     */
    public function __construct( $form )
    {
        $this->form = $form;
        $this->style = 'password';
    }
}