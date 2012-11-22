<?php
namespace Interaction;

class view1 
{
    /** @var \wsModule\Alt\Html\View_Bootstrap */
    private $view;

    /** @var \WScore\DbAccess\Role */
    private $role;

    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\Role $role
     * @param \wsModule\Alt\Html\View_Bootstrap $view
     * @DimInjection Get   \WScore\DbAccess\Role
     * @DimInjection Fresh \wsModule\Alt\Html\View_Bootstrap
     */
    public function __construct( $role, $view ) {
        $this->role = $role;
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

    // +----------------------------------------------------------------------+
    //  building views for forms etc.
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\Role_Input $entity
     */
    public function showForm_form( $entity )
    {
        $role = $this->role->applySelectable( $entity );
        $role->setHtmlType( 'form' );
        if( !$role->isValid() ) {
            $this->set( 'alert-error', 'please submit the form again. ' );
        }
        $this->set( 'entity', $role );
        $this->set( 'title', 'Friend Form' );
        $this->set( 'button-primary', 'confirm information' );
        $this->set( 'button-sub', 'reset' );
    }
    /**
     * @param \WScore\DbAccess\Role_Input $entity
     */
    public function showForm_confirm( $entity )
    {
        $entity = $this->role->applySelectable( $entity );
        $entity->setHtmlType( 'html' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Confirmation of Inputs' );
        $this->set( 'button-primary', 'save the information' );
        $this->set( 'button-sub', '/WScore/interaction1.php?action=form' );
    }

    /**
     * @param \WScore\DbAccess\Role_Input $entity
     */
    public function showForm_done( $entity )
    {
        $entity = $this->role->applySelectable( $entity );
        $entity->setHtmlType( 'html' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'completed' );
    }
    // +----------------------------------------------------------------------+
}

