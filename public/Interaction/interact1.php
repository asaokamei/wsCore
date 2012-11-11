<?php
namespace Interaction;

class interact1 extends \wsCore\Web\Interaction
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
            array( 'formLoad',    'form',     'load',  ),
            array( 'pushToken',   'confirm',    ),
            array( 'verifyToken', 'save',     'done' ),
        );
        $result = $this->webFormWizard( $view, $entity, $action, $steps );
        if( $result == 'save' ) {
            $active = $this->context->applyActive( $entity );
            $active->save();
            $view->set( 'alert-success', 'your friend information is saved. ' );
        }
        elseif( $result === false ) {
            $view->set( 'alert-info', 'your friend information has been already saved. ' );
        }
        return $entity;
    }
}
