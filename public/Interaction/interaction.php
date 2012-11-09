<?php
namespace Interaction;

class interact extends \wsCore\Web\Interaction
{
    /**
     * @param $entity
     * @param $role
     * @return \role
     */
    public function applyContext( $entity, $role ) {
        if( $role == 'loadable' ) {
            return $this->context->applyLoadable( $entity );
        }
        return $entity;
    }

    /**
     * @param string $action
     * @param \Interaction\view $view
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
        if( $this->actionFormAndLoad( $view, $role, $action, 'wizard1', 'load1' ) ) return $entity;
        if( $this->actionFormAndLoad( $view, $role, $action, 'wizard2', 'load2' ) ) return $entity;
        if( $this->actionFormAndLoad( $view, $role, $action, 'wizard3', 'load3' ) ) return $entity;

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
    /**
     * insert data with steps: form -> confirm -> insert
     *
     * @param string $action
     * @param \Interaction\View $view
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
