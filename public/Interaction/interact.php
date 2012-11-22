<?php
namespace Interaction;

class interact extends \wsModule\Alt\Web\Interaction
{
    /**
     * @param \wsModule\Alt\Web\Request   $request
     * @param \WScore\Web\Session $session
     * @param \WScore\DbAccess\Role $role
     * @param \Interaction\view1 $view
     * @DimInjection Get   \wsModule\Alt\Web\Request
     * @DimInjection Fresh Session
     * @DimInjection Get   \WScore\DbAccess\Role
     * @DimInjection Get   interactView
     */
    public function __construct( $request, $session, $role, $view ) {
        $this->request = $request;
        $this->session = ($session) ?: $_SESSION;
        $this->role = $role;
        $this->view    = $view;
    }

    public function saveEntity3Steps( $action )
    {
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->role->em->newEntity( 'Interaction\model' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        if( $this->contextFormAndLoad( $entity, $action, 'form' ) ) {
            $role = $this->role->applyInputAndSelectable( $entity );
            $this->view->set( 'action', 'form' );
            $this->view->showForm( $role, 'form' );
            return;
        }
        if( $this->contextValidateAndPushToken( $entity, $action, 'save' ) ) {
            $role = $this->role->applyInputAndSelectable( $entity );
            $this->view->set( 'action', 'save' );
            $this->view->showForm( $role, 'confirm' );
            return;
        }
        if( $this->contextVerifyTokenAndSave( $entity, $action, 'save' ) ) {
            $this->view->set( 'alert-success', 'your friendship has been saved. ' );
        }
        else {
            $this->view->set( 'alert-info', 'your friendship has already been saved. ' );
        }
        $role = $this->role->applyInputAndSelectable( $entity );
        $this->view->showForm( $role, 'done' );

        return;
    }

    /**
     * @param \Interaction\entity $entity
     * @param string $action
     * @return bool|string
     */
    function contextForms( $entity, $action )
    {
        if( $this->restoreData( 'done' ) ) {
            $action = 'save';
        }
        $steps = array(
            array( 'formLoad',    'form',     'load',  ),
            array( 'pushToken',   'confirm',  'save'  ),
            array( 'verifyToken', 'save',     'done' ),
        );
        $result = $this->webFormWizard( $entity, $action, $steps );
        return $result;
    }

    /**
     * @param \Interaction\entity $entity
     * @param string $action
     * @return bool|string
     */
    function contextWizard( $entity, $action )
    {
        if( $this->restoreData( 'done' ) ) {
            $action = 'save';
        }
        $steps = array(
            array( 'formLoad',    'wizard1',  'load1',  ),
            array( 'formLoad',    'wizard2',  'load2',  ),
            array( 'formLoad',    'wizard3',  'load3',  ),
            array( 'pushToken',   'confirm',  'save',    ),
            array( 'verifyToken', 'save',     'done' ),
        );
        $result = $this->webFormWizard( $entity, $action, $steps );
        return $result;
    }
    
    /**
     * @param string $action
     * @return \Interaction\entity
     */
    function insert( $action ) {
        return $this->genericInsert( $action, 'contextForms' );
    }

    /**
     * @param string $action
     * @return \Interaction\entity
     */
    function wizard( $action ) {
        return $this->genericInsert( $action, 'contextWizard' );
    }

    /**
     * @param string $action
     * @param string $context
     * @return \Interaction\entity
     */
    function genericInsert( $action, $context )
    {
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->role->em->newEntity( 'Interaction\model' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        $result = $this->$context( $entity, $action );
        if( $result == 'save' ) {
            $active = $this->role->applyActive( $entity );
            $active->save();
            $this->view->set( 'alert-success', 'your friendship has been saved. ' );
        }
        elseif( $result === false ) {
            $this->view->set( 'alert-info', 'your friendship has already been saved. ' );
        }
        return $entity;
    }
}