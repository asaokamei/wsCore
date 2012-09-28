<?php
namespace wsCore\Html;

class Text extends Selector
{
    /**
     * @param Form $form
     */
    public function __construct( $form )
    {
        $this->form = $form;
        $this->style = 'text';
    }
}