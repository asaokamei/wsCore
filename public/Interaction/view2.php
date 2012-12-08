<?php
namespace Interaction;

class view2 extends \wsModule\Alt\Html\View_Bootstrap
{

    /** @var \WScore\DataMapper\Role */
    private $role;

    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\Role $role
     * @DimInjection Get   \WScore\DataMapper\Role
     */
    public function __construct( $role ) {
        parent::__construct();
        $this->role = $role;
    }

    /**
     * @param \WScore\DataMapper\Role_Input $entity
     * @param string $form
     * @return void
     */
    public function showForm( $entity, $form )
    {
        $role = $this->role->applySelectable( $entity );
        $role->setHtmlType( 'form' );
        if( !$role->isValid() ) {
            $this->set( 'alert-error', 'please submit the form again. ' );
        }
        $this->set( 'currAction', $form );
        $this->set( 'entity', $role );
        $this->set( 'title', $form );
        $show = 'showForm_' . $form;
        $this->$show( $entity );
    }

    /**
     * @param \WScore\DataMapper\Role_Input $entity
     */
    public function showForm_wizard1( $entity )
    {
        $this->set( 'button-primary', 'next' );
        $this->set( 'button-sub', '' );
        $this->set( 'title', 'Friend Form#1' );
    }

    /**
     * @param \WScore\DataMapper\Role_Input $entity
     */
    public function showForm_wizard2( $entity )
    {
        $this->set( 'button-primary', 'next' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard1' );
        $this->set( 'title', 'Friend Form#2' );
    }

    /**
     * @param \WScore\DataMapper\Role_Input $entity
     */
    public function showForm_wizard3( $entity )
    {
        $this->set( 'button-primary', 'confirm inputs' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard2' );
        $this->set( 'title', 'Friend Form#3' );
    }

    /**
     * @param \WScore\DataMapper\Role_Input $entity
     */
    public function showForm_confirm( $entity )
    {
        $role = $this->role->applySelectable( $entity );
        $role->setHtmlType( 'html' );
        $this->set( 'entity', $role );
        $this->set( 'title', 'Confirmation of Inputs' );
        $this->set( 'button-primary', 'save the information' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard3' );
    }

    /**
     * @param \WScore\DataMapper\Role_Input $entity
     */
    public function showForm_done( $entity )
    {
        $role = $this->role->applySelectable( $entity );
        $role->setHtmlType( 'html' );
        $this->set( 'entity', $role );
        $this->set( 'title', 'Completed' );
    }
}

