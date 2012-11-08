<?php
namespace Interaction;

require_once( __DIR__ . '/model.php' );
require_once( __DIR__ . '/entity.php' );

class view extends \wsCore\Html\PageView
{
    public $view = array();
    public function showForm( $entity, $form ) {
        $this->set( 'currAction', $form );
        $this->set( 'entity', $entity );
        $this->set( 'title', $form );
        $show = 'showForm_' . $form;
        $this->$show( $entity );
    }
    public function showForm_form( $entity )
    {
        $this->set( 'action', 'load' );
        $this->view[ 'title' ] = 'Friend Form';
    }
    public function showForm_wizard1( $entity )
    {
        $this->set( 'action', 'load1' );
    }
    public function showForm_wizard2( $entity )
    {
        $this->set( 'action', 'load2' );
    }
    public function showForm_wizard3( $entity )
    {
        $this->set( 'action', 'load3' );
    }
    public function showConfirm( $entity ) {
        $this->set( 'currAction', 'confirm' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'confirm' );
        $this->set( 'action', 'save' );
    }
    public function showDone( $entity ) {
        $this->set( 'currAction', 'done' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'done' );
        $this->set( 'action', 'done' );
    }
    public function setToken( $token ) {
        $this->view[ 'token' ] = $token;
    }
}

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
     * @return \Interaction\view
     */
    function wizard( $action, $view )
    {
        // get entity
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->context->newEntity( 'entity' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        elseif( $this->restoreData( 'complete' ) ) {
            goto done;
        }
        if( $this->actionFormAndLoad( $view, $entity, $action, 'wizard1', 'load1' ) ) return $view;
        if( $this->actionFormAndLoad( $view, $entity, $action, 'wizard2', 'load2' ) ) return $view;
        if( $this->actionFormAndLoad( $view, $entity, $action, 'wizard3', 'load3' ) ) return $view;

        // show confirm except for save.
        if( $action != 'save' ) {
            $view->set( $this->session->popTokenTagName(), $this->session->pushToken() );
            $view->showConfirm( $entity );
            return $view;
        }
        // save entity.
        if( $action == 'save' && $this->verifyToken() ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
        }
        // done
        done :
        $view->showDone( $entity );
        return $view;
    }
    /**
     * insert data with steps: form -> confirm -> insert
     *
     * @param string $action
     * @param \Interaction\View $view
     * @return \Interaction\View
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
        elseif( $this->restoreData( 'complete' ) ) {
            goto done;
        }
        if( $this->actionFormAndLoad( $view, $entity, $action, 'form', 'load' ) ) return $view;

        // show confirm except for save.
        if( $action != 'save' ) {
            $view->setToken( $this->makeToken() );
            $view->showConfirm( $entity );
            return $view;
        }
        // save entity.
        if( $action == 'save' && $this->verifyToken() ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
        }
        // done
        done :
        $view->showDone( $entity );
        return $view;
    }

}
