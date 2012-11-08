<?php
namespace Interaction;

require_once( __DIR__ . '/model.php' );
require_once( __DIR__ . '/entity.php' );

class view extends \wsCore\Html\PageView
{
    public $view = array();

    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     * @param string $form
     * @return void
     */
    public function showForm( $entity, $form )
    {
        if( !$entity->isValid() ) {
            $this->set( 'alert-error', 'please submit the form again. ' );
        }
        $this->set( 'currAction', $form );
        $this->set( 'entity', $entity );
        $this->set( 'title', $form );
        $show = 'showForm_' . $form;
        $this->$show( $entity );
    }

    /**
     * @param \wsCore\DbAccess\Context_RoleInput $entity
     */
    public function showForm_form( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load' );
        $this->view[ 'title' ] = 'Friend Form';
    }
    public function showForm_wizard1( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load1' );
    }
    public function showForm_wizard2( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load2' );
    }
    public function showForm_wizard3( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load3' );
    }
    public function showConfirm( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'currAction', 'confirm' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Confirmation of Inputs' );
        $this->set( 'action', 'save' );
    }
    public function showDone( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'currAction', 'done' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Completed' );
        $this->set( 'action', 'done' );
    }

    // boot strap thingy.
    public function bootstrapAlertSuccess() {
        $message = $this->get( 'alert-success' );
        if( !$message ) return '';
        $title   = 'Message:';
        return $this->bootstrapAlert( 'error', $message, $title );
    }

    public function bootstrapAlertInfo() {
        $message = $this->get( 'alert-info' );
        if( !$message ) return '';
        $title   = 'Notice:';
        return $this->bootstrapAlert( 'error', $message, $title );
    }

    public function bootstrapAlertError() {
        $message = $this->get( 'alert-error' );
        if( !$message ) return '';
        $title   = 'Error Message:';
        return $this->bootstrapAlert( 'error', $message, $title );
    }

    public function bootstrapAlert( $type, $message, $title=null ) {
        if( !$title ) $title = 'Warning!';
        $html = "
          <div class=\"alert {$type}\">
            <h4>{$title}</h4>
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
            {$message}
          </div>";
        return $html;
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
            $this->registerData( 'complete', true );
        }
        // done
        done :
        $view->showDone( $role );
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
        $role = $this->context->applyLoadable( $entity );
        if( $this->actionFormAndLoad( $view, $role, $action, 'form', 'load' ) ) return $view;

        // show confirm except for save.
        if( $action != 'save' ) {
            $view->set( $this->session->popTokenTagName(), $this->session->pushToken() );
            $view->showConfirm( $role );
            return $view;
        }
        // save entity.
        if( $action == 'save' && $this->verifyToken() ) {
            $active = $this->applyContext( $entity, 'active' );
            $active->insert();
            $view->set( 'alert-success', 'your friendship has been saved. ' );
        }
        // done
        done :
        $view->showDone( $role );
        return $view;
    }

}
