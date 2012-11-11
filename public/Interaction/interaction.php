<?php
namespace Interaction;

class interact extends \wsCore\Web\Interaction
{
    /**
     * @param string $action
     * @param \Interaction\view1 $view
     * @return \Interaction\entity
     */
    function wizard2( $action, $view )
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
    
    /**
     * @param string $action
     * @param \Interaction\view1 $view
     * @return \Interaction\entity
     */
    function wizard( $action, $view )
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
        if( $this->actionFormAndLoad( $view, $role, $action, 'wizard1',       'load1' ) ) return $entity;
        if( $this->actionFormAndLoad( $view, $role, $action, 'wizard2|load1', 'load2' ) ) return $entity;
        if( $this->actionFormAndLoad( $view, $role, $action, 'wizard3|load2', 'load3' ) ) return $entity;

        // show confirm except for save.
        if( $action != 'save' ) {
            $view->set( $this->session->popTokenTagName(), $this->session->pushToken() );
            $view->showForm( $role, 'confirm' );
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
