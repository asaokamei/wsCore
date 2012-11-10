<?php
namespace Interaction;

class view1 
{
    private $view;

    /**
     * @param \Interaction\View_Bootstrap $view
     * @DimInjection Fresh \Interaction\View_Bootstrap
     */
    public function __construct( $view ) {
        $this->view = $view;
    }

    /**
     * @return \Interaction\View_Bootstrap
     */
    public function getView() {
        return $this->view;
    }

    /**
     * set state of the resource.
     *
     * @param $name
     * @param $value
     * @return view1
     */
    public function set( $name, $value )
    {
        $this->view->set( $name, $value );
        return $this;
    }

    /**
     * get state of the top resources.
     *
     * @param $name
     * @return mixed
     */
    public function get( $name ) {
        return $this->view->get( $name );
    }
    
    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
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
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     */
    public function showForm_form( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load' );
        $this->set( 'title', 'Friend Form' );
        $this->set( 'button-primary', 'confirm information' );
        $this->set( 'button-sub', 'reset' );
    }
    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     */
    public function showConfirm( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'currAction', 'confirm' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Confirmation of Inputs' );
        $this->set( 'action', 'save' );
        $this->set( 'button-primary', 'save the information' );
        $this->set( 'button-sub', 'back' );
    }

    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     */
    public function showDone( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'currAction', 'done' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Completed' );
        $this->set( 'action', 'done' );
    }
}

class view2 extends View_Bootstrap
{
    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
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
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     */
    public function showForm_wizard1( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load1' );
        $this->set( 'button-primary', 'next' );
        $this->set( 'button-sub', '' );
        $this->set( 'title', 'Friend Form#1' );
    }

    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     */
    public function showForm_wizard2( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load2' );
        $this->set( 'button-primary', 'next' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard1' );
        $this->set( 'title', 'Friend Form#2' );
    }

    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     */
    public function showForm_wizard3( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load3' );
        $this->set( 'button-primary', 'confirm inputs' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard2' );
        $this->set( 'title', 'Friend Form#3' );
    }

    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     */
    public function showConfirm( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'currAction', 'confirm' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Confirmation of Inputs' );
        $this->set( 'action', 'save' );
        $this->set( 'button-primary', 'save the information' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard3' );
    }

    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     */
    public function showDone( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'currAction', 'done' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Completed' );
        $this->set( 'action', 'done' );
    }
}

