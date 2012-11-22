<?php
namespace Interaction;

class interact extends \wsModule\Alt\Web\Interaction
{
    /**
     * @param \wsModule\Alt\Web\Request   $request
     * @param \WScore\Web\Session $session
     * @param \WScore\DbAccess\Role $role
     * @param \Interaction\view1|\Interaction\view2 $view
     * @DimInjection Get   \wsModule\Alt\Web\Request
     * @DimInjection Fresh Session
     * @DimInjection Get   \WScore\DbAccess\Role
     * @DimInjection Get   interactView
     */
    public function __construct( $request, $session, $role, $view )
    {
        parent::__construct( $request, $session, $role );
        $this->view    = $view;
    }

    /**
     * inserts entity in 3 steps: form -> confirm -> save/done.
     *
     * @param string $action
     */
    public function saveEntity3Steps( $action )
    {
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->role->em->newEntity( 'Interaction\model' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        if( $this->contextFormAndLoad( $entity, $action, 'form' ) ) {
            $this->view->set( 'action', 'form' );
            $this->view->showForm_form( $entity, 'form' );
            return;
        }
        if( $this->contextValidateAndPushToken( $entity, $action, 'save' ) ) {
            $this->view->set( 'action', 'save' );
            $this->view->showForm_confirm( $entity );
            return;
        }
        if( $this->contextVerifyTokenAndSave( $entity, $action, 'save' ) ) {
            $this->view->set( 'alert-success', 'your friendship has been saved. ' );
        }
        else {
            $this->view->set( 'alert-info', 'your friendship has already been saved. ' );
        }
        $this->view->showForm_done( $entity );

        return;
    }

    /**
     * wizard-like interactions for inserting an entity. steps are:
     * wizard1 -> wizard2 -> wizard3 -> confirm -> save/done.
     *
     * @param string $action
     */
    public function saveWizards( $action )
    {
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->role->em->newEntity( 'Interaction\model' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        $forms = array( 'wizard1', 'wizard2', 'wizard3', );
        foreach( $forms as $form ) {
            if( $this->contextFormAndLoad( $entity, $action, $form ) ) {
                $this->view->set( 'action', $form );
                $this->view->showForm( $entity, $form );
                return;
            }
        }
        if( $this->contextValidateAndPushToken( $entity, $action, 'save' ) ) {
            $this->view->set( 'action', 'save' );
            $this->view->showForm( $entity, 'confirm' );
            return;
        }
        if( $this->contextVerifyTokenAndSave( $entity, $action, 'save' ) ) {
            $this->view->set( 'alert-success', 'your friendship has been saved. ' );
        }
        else {
            $this->view->set( 'alert-info', 'your friendship has already been saved. ' );
        }
        $this->view->showForm( $entity, 'done' );

        return;
    }
    // +----------------------------------------------------------------------+
}