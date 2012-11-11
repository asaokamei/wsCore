<?php
namespace Interaction;

class interact2 extends \wsCore\Web\Interaction
{
    /**
     * @param \wsCore\Web\Session $session
     * @param \wsCore\DbAccess\Context $context
     * @param \Interaction\view2 $view
     * @DimInjection Fresh Session
     * @DimInjection Get   \wsCore\DbAccess\Context
     * @DimInjection Get   \Interaction\view2
     */
    public function __construct( $session, $context, $view ) {
        parent::__construct( $session, $context );
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
            array( 'formLoad',    'wizard1',  'load1',  ),
            array( 'formLoad',    'wizard2',  'load2',  ),
            array( 'formLoad',    'wizard3',  'load3',  ),
            array( 'pushToken',   'confirm',  'load3',    ),
            array( 'verifyToken', 'save',     'done' ),
        );
        $result = $this->webFormWizard( $entity, $action, $steps );
        if( $result == 'save' ) {
            $active = $this->context->applyActive( $entity );
            $active->save();
            $this->view->set( 'alert-success', 'your friendship has been saved. ' );
        }
        elseif( $result === false ) {
            $this->view->set( 'alert-info', 'your friendship has already been saved. ' );
        }
        return $entity;
    }
}
