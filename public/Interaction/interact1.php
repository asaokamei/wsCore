<?php
namespace Interaction;

class interact1 extends \wsCore\Web\Interaction
{
    /**
     * @param \wsCore\Web\Session $session
     * @param \wsCore\DbAccess\Context $context
     * @param \Interaction\view1 $view
     * @DimInjection Fresh Session
     * @DimInjection Get   \wsCore\DbAccess\Context
     * @DimInjection Get   \Interaction\view1
     */
    public function __construct( $session, $context, $view ) {
        $this->session = ($session) ?: $_SESSION;
        $this->context = $context;
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
            $entity = $this->context->newEntity( 'model' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        $steps = array(
            array( 'formLoad',    'form',     'load',  ),
            array( 'pushToken',   'confirm',    ),
            array( 'verifyToken', 'save',     'done' ),
        );
        $result = $this->webFormWizard( $entity, $action, $steps );
        if( $result == 'save' ) {
            $active = $this->context->applyActive( $entity );
            $active->save();
            $this->view->set( 'alert-success', 'your friend information is saved. ' );
        }
        elseif( $result === false ) {
            $this->view->set( 'alert-info', 'your friend information has been already saved. ' );
        }
        return $entity;
    }
}
