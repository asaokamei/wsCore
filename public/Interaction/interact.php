<?php
namespace Interaction;

class interact extends \wsModule\Alt\Web\Interaction
{
    /** @var \WScore\Html\PageView */
    protected $view;

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
     * @return view1|view2|\WScore\Html\PageView
     */
    public function getView() {
        return $this->view;
    }

    /**
     * inserts entity in 3 steps: form -> confirm -> save/done.
     *
     * @param string $action
     */
    public function saveEntity3Steps( $action )
    {
        // get entity from saved in the session.
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            // create new entity.
            $this->clearData();
            $entity = $this->role->em->newEntity( 'Interaction\model' );
            $this->registerData( 'entity', $entity );
        }
        // show the input form. also load and validates the input.
        if( $this->contextFormAndLoad( $entity, $action, 'form' ) ) {
            $this->view->set( 'action', 'form' );
            $this->view->showForm_form( $entity, 'form' );
            return;
        }
        // show the confirm view. save token as well.
        if( $this->contextValidateAndPushToken( $entity, $action, 'save' ) ) {
            $token = $this->session->pushToken();
            $this->view->set( $this->session->popTokenTagName(), $token );
            $this->view->set( 'action', 'save' );
            $this->view->showForm_confirm( $entity );
            return;
        }
        // save the entity.
        if( $this->session->verifyToken() && $this->contextVerifyTokenAndSave( $entity, $action, 'save' ) ) {
            // saved just now.
            $this->view->set( 'alert-success', 'your friendship has been saved. ' );
        }
        else {
            // already saved.
            $this->view->set( 'alert-info', 'your friendship has already been saved. ' );
        }
        // show done view.
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
        // get entity from saved in the session.
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $this->clearData();
            $entity = $this->role->em->newEntity( 'Interaction\model' );
            $this->registerData( 'entity', $entity );
        }
        // show the wizard form. load and validates the input for each form.
        $forms = array( 'wizard1', 'wizard2', 'wizard3', );
        if( $form = $this->formWizards( $entity, $action, $forms ) ) {
            $this->view->set( 'action', $form );
            $this->view->showForm( $entity, $form );
            return;
        }
        // show the confirm view. save token as well.
        if( $this->contextValidateAndPushToken( $entity, $action, 'save' ) ) {
            $token = $this->session->pushToken();
            $this->view->set( $this->session->popTokenTagName(), $token );
            $this->view->set( 'action', 'save' );
            $this->view->showForm( $entity, 'confirm' );
            return;
        }
        // save the entity.
        if( $this->session->verifyToken() && $this->contextVerifyTokenAndSave( $entity, $action, 'save' ) ) {
            $this->view->set( 'alert-success', 'your friendship has been saved. ' );
        }
        else {
            $this->view->set( 'alert-info', 'your friendship has already been saved. ' );
        }
        // show done view.
        $this->view->showForm( $entity, 'done' );

        return;
    }
    // +----------------------------------------------------------------------+
}