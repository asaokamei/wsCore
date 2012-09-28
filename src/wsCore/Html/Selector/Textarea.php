<?php
namespace wsCore\Html;

class Textarea extends Selector
{
    /**
     * @param Form $form
     */
    public function __construct( $form )
    {
        $this->form = $form;
        $this->style = 'textarea';
    }
}