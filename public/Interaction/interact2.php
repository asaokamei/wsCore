<?php
namespace Interaction;

class interact2 extends \wsCore\Web\Interaction
{
    /**
     * @param string $action
     * @param \Interaction\view1 $view
     * @return \Interaction\entity
     */
    function wizard( $action, $view )
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
        $result = $this->webFormWizard( $view, $entity, $action, $steps );
        if( $result == 'save' ) {
            $active = $this->context->applyActive( $entity );
            $active->save();
            $view->set( 'alert-success', 'your friendship has been saved. ' );
        }
        elseif( $result === false ) {
            $view->set( 'alert-info', 'your friendship has already been saved. ' );
        }
        return $entity;
    }
}
