<?php
namespace Interaction;

class interact1 extends \wsCore\Web\Interaction
{
    /**
     * @param \wsCore\Web\Session $session
     * @param \wsCore\DbAccess\Role $role
     * @param \Interaction\view1 $view
     * @DimInjection Fresh Session
     * @DimInjection Get   \wsCore\DbAccess\Role
     * @DimInjection Get   \Interaction\view1
     */
    public function __construct( $session, $role, $view ) {
        $this->session = ($session) ?: $_SESSION;
        $this->role = $role;
        $this->view    = $view;
    }
    
    /**
     * @param string $action
     * @return \Interaction\entity
     */
    function wizard( $action )
    {
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->role->newEntity( 'model' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        if( $this->restoreData( 'done' ) ) {
            $action = 'save';
        }
        $steps = array(
            array( 'formLoad',    'form',     'load',  ),
            array( 'pushToken',   'confirm',  'save'  ),
            array( 'verifyToken', 'save',     'done' ),
        );
        $result = $this->webFormWizard( $entity, $action, $steps );
        if( $result == 'save' ) {
            $active = $this->role->applyActive( $entity );
            $active->save();
            $this->view->set( 'alert-success', 'your friend information is saved. ' );
        }
        elseif( $result === false ) {
            $this->view->set( 'alert-info', 'your friend information has been already saved. ' );
        }
        return $entity;
    }
}
