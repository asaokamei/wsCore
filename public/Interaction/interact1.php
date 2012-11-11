<?php
namespace Interaction;

class interact1 extends \wsCore\Web\Interaction
{
    /**
     * insert data with steps: form -> confirm -> insert
     *
     * @param string $action
     * @param \Interaction\view1 $view
     * @return \Interaction\entity
     */
    function insertData( $action, $view )
    {
        // get entity
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->context->newEntity( 'model' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        $role = $this->context->applyLoadable( $entity );
        if( $this->restoreData( 'complete' ) ) {
            goto done;
        }
        if( $this->actionFormAndLoad( $view, $role, $action, 'form', 'load' ) ) return $entity;

        // show confirm except for save.
        if( $action != 'save' ) {
            $view->set( $this->session->popTokenTagName(), $this->session->pushToken() );
            $view->showConfirm( $role );
            return $entity;
        }
        // save entity.
        if( $action == 'save' && $this->verifyToken() ) {
            $active = $this->context->applyActive( $entity );
            $active->save();
            $this->registerData( 'complete', true );
            $view->set( 'alert-success', 'your friendship has been saved. ' );
            $view->showDone( $role );
            return $entity;
        }
        // done
        done :
        $view->set( 'alert-info', 'your friendship has already been saved. ' );
        $view->showDone( $role );
        return $entity;
    }

}
