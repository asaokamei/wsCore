<?php
namespace Interaction;

class view2 extends \wsModule\Alt\Html\View_Bootstrap
{
    /**
     * @param \wsCore\DbAccess\Role_Input $entity
     * @param string $form
     * @return void
     */
    public function showForm( $entity, $form )
    {
        if( !$entity->isValid() ) {
            $this->set( 'alert-error', 'please submit the form again. ' );
        }
        $this->set( 'currAction', $form );
        $this->set( 'entity', $entity );
        $this->set( 'title', $form );
        $show = 'showForm_' . $form;
        $this->$show( $entity );
    }

    /**
     * @param \wsCore\DbAccess\Role_Input $entity
     */
    public function showForm_wizard1( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'button-primary', 'next' );
        $this->set( 'button-sub', '' );
        $this->set( 'title', 'Friend Form#1' );
    }

    /**
     * @param \wsCore\DbAccess\Role_Input $entity
     */
    public function showForm_wizard2( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'button-primary', 'next' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard1' );
        $this->set( 'title', 'Friend Form#2' );
    }

    /**
     * @param \wsCore\DbAccess\Role_Input $entity
     */
    public function showForm_wizard3( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'button-primary', 'confirm inputs' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard2' );
        $this->set( 'title', 'Friend Form#3' );
    }

    /**
     * @param \wsCore\DbAccess\Role_Input $entity
     */
    public function showForm_confirm( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Confirmation of Inputs' );
        $this->set( 'button-primary', 'save the information' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard3' );
    }

    /**
     * @param \wsCore\DbAccess\Role_Input $entity
     */
    public function showForm_done( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Completed' );
    }
}

