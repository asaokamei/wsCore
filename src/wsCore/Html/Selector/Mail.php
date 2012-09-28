<?php
namespace wsCore\Html;

class Mail extends Selector
{
    /**
     * @param Form $form
     */
    public function __construct( $form )
    {
        $this->form = $form;
        $this->style = 'mail';
    }
}