<?php
namespace interaction;

class entity
{
    public $entityName = null;
    public $role = null;
    public $_actions = array();
    public function loadData( $load ) {
        $this->_actions[] = $load;
    }
    public function verify( $load ) {
        $this->_actions[] = $load;
        return !\wsCore\Utilities\Tools::getKey( $_REQUEST, 'error' );
    }
}

class view
{
    public $view = array();
    public function showForm( $entity, $form ) {
        $actions = array(
            'form'  => 'confirm',
            'form1' => 'form2',
            'form2' => 'confirm',
        );
        $this->view[ 'entity' ] = $entity;
        $this->view[ 'title' ] = $form;
        $this->view[ 'action' ] = $actions[ $form ];
    }
    public function showConfirm( $entity ) {
        $this->view[ 'entity' ] = $entity;
        $this->view[ 'title' ] = 'confirm';
        $this->view[ 'action' ] = 'save';
    }
    public function showDone( $entity ) {
        $this->view[ 'entity' ] = $entity;
        $this->view[ 'title' ] = 'done';
        $this->view[ 'action' ] = 'save';
    }
    public function setToken( $token ) {
        $this->view[ 'token' ] = $token;
    }
}

class interact extends \wsCore\Web\Interaction
{

    /**
     * @param string $action
     * @param \dci\view $view
     * @return \dci\view
     */
    function insertData( $action, $view )
    {
        // get entity
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->contextGet( 'entity' );
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
            return $view->showConfirm( $entity );
        }
        // save entity.
        if( $action == 'save' && $this->verifyToken() ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
        }
        // done
        done :
        return $view->showDone( $entity );
    }

}
