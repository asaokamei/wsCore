<?php
namespace Interaction;

class view1 
{
    private $view;

    /**
     * @param \wsModule\Alt\Html\View_Bootstrap $view
     * @DimInjection Fresh \wsModule\Alt\Html\View_Bootstrap
     */
    public function __construct( $view ) {
        $this->view = $view;
    }

    /**
     * @return \wsModule\Alt\Html\View_Bootstrap
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
     * @param \WScore\DbAccess\Role_Input $entity
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
     * @param \WScore\DbAccess\Role_Input $entity
     */
    public function showForm_form( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'title', 'Friend Form' );
        $this->set( 'button-primary', 'confirm information' );
        $this->set( 'button-sub', 'reset' );
    }
    /**
     * @param \WScore\DbAccess\Role_Input $entity
     */
    public function showForm_confirm( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Confirmation of Inputs' );
        $this->set( 'button-primary', 'save the information' );
        $this->set( 'button-sub', 'back' );
    }

    /**
     * @param \WScore\DbAccess\Role_Input $entity
     */
    public function showForm_done( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Completed' );
    }
}

