<?php

class Interaction
{
    protected $variables = array();
    
    protected $states = array();
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    public function __construct() {
    }
    public static function load( $class ) {
        $class = self::saveName( $class );
        $object = unserialize( $_SESSION[ $class ] );
        return $object;
    }
    public function save() {
        $class = self::saveName( get_called_class() );
        $_SESSION[ $class ] = serialize( $this );
    }
    protected static function saveName( $class ) {
        $class = str_replace( '\\', '__', $class );
        return $class;
    }
    // +----------------------------------------------------------------------+
    public function setState( $states ) {
        $this->states = $states;
    }
    public function getState() {
        return isset( $this->states[0] ) ? $this->states[0]: null;
    }
    public function checkState( $state ) {
        if( $state == $this->states[0] ) {
            return TRUE;
        }
        return FALSE;
    }
    public function nextState() {
        array_splice( $this->states, 0, 1 );
    }
    public function execStateOrControl( $control, $state ) {
        $status = FALSE;
        if( $control== $state ) $status = TRUE;
        if( $this->checkState( $state ) ) {
            $this->nextState();
            $status = TRUE;
        }
        return $status;
    }
    // +----------------------------------------------------------------------+
    //  variables
    // +----------------------------------------------------------------------+
    public function register( $name, $data ) {
        $this->variables[ $name ] = $data;
    }
    public function restore( $name ) {
        return $this->variables[ $name ];
    }
    // +----------------------------------------------------------------------+
    public function applyContext( $entity, $role ) {
        return $entity;
    }
    public function contextGet( $entityName ) {
        return $entityName;
    }
    // +----------------------------------------------------------------------+
}

class view 
{
    function showForm1() { return $this; }
    function showForm2() { return $this; }
    function showConfirm() { return $this; }
    function showDone() { return $this; }
    
}

class controlEntity extends Interaction
{
    /**
     * @param string $control
     * @param view $view
     * @return \view
     */
    function entityAdd( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        $state  = $this->getState();
        if( !$state ) {
            $entity = $this->contextGet( 'entity' );
            $this->register( 'entity', $entity );
            $this->setState( [ 'form1', 'form2', 'confirm', 'save', 'done' ] );
        }
        $role = $this->applyContext( $entity, 'loadable' );
        // form1
        if( $control == 'form1' || $state == 'form1' ) {
            $this->nextState();
            return $view->showForm1( $entity );
        }
        if( $control == 'load1' ) $role->load( 'load1' );
        if( !$role->verify( 'load1' ) ) return $view->showForm1( $entity );

        // form2
        if( $control == 'form2' || $state == 'form2' ) {
            $this->nextState();
            return $view->showForm2( $entity );
        }
        if( $control == 'load2' ) $role->load( 'load2' );
        if( !$role->verify( 'load2' ) ) return $view->showForm1( $entity );

        // confirm
        if( $state == 'confirm' ) {
            if( $control != 'save' ) {
                return $view->showConfirm( $entity );
            }
            $this->nextState();
        }
        // save
        if( $state == 'save' ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
            $this->nextState();
        }

        // done
        $view->showDone( $entity );
    }
    /**
     * @param string $control
     * @param view $view
     * @return \view
     */
    function entityAddSimple( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        $state  = $this->getState();
        if( !$state ) {
            $entity = $this->contextGet( 'entity' );
            $this->register( 'entity', $entity );
            $this->setState( [ 'forms', 'save', 'done' ] );
        }
        $role = $this->applyContext( $entity, 'loadable' );
        // form1
        if( $control == 'form1' ) {
            return $view->showForm1( $entity );
        }
        if( $control == 'load1' ) $role->load( 'load1' );
        if( !$role->verify( 'load1' ) ) return $view->showForm1( $entity );

        // form2
        if( $control == 'form2' ) {
            return $view->showForm2( $entity );
        }
        if( $control == 'load2' ) $role->load( 'load2' );
        if( !$role->verify( 'load2' ) ) return $view->showForm1( $entity );

        // confirm
        if( $control == 'confirm' ) {
            $this->nextState();
            return $view->showConfirm( $entity );
        }
        // save
        if( $control == 'save' && $state == 'save' ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
            $this->nextState();
        }

        // done
        $view->showDone( $entity );
    }
}

class entityManyForms extends Interaction
{
    /**
     * @param string $control
     * @param view $view
     */
    function entityAdd_bare( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        $role = $this->applyContext( $entity, 'loadable' );
        
        // form1
        $role->verify( 'form1' );
        
        // form2
        $role->verify( 'form2' );
        
        // confirm
        // $role->verify();  // should not need it...
        $view->showConfirm( $entity );
        
        // save
        $role = $this->applyContext( $entity, 'active' );
        $role->insert();
        
        // done
        $view->showDone( $entity );
    }
    /**
     * @param string $control
     * @param view $view
     * @return \view
     */
    function entityAdd_1st( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        $role = $this->applyContext( $entity, 'loadable' );

        // form1
        $view->showForm1( $entity );
        $role->load( 'form1' );
        $role->verify( 'form1' );

        // form2
        $view->showForm2( $entity );
        $role->load( 'form2' );
        $role->verify( 'form2' );

        // confirm
        $view->showConfirm( $entity );

        // save
        $role = $this->applyContext( $entity, 'active' );
        $role->insert();

        // done
        $view->showDone( $entity );
    }
    /**
     * @param string $control
     * @param view $view
     * @return \view
     */
    function entityAdd_2nd( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        if( !$entity ) {
            $entity = $this->contextGet( 'entity' );
            $this->register( 'entity', $entity );
            $this->setState( [ 'form1', 'form2', 'confirm', 'save', 'done' ] );
        }
        $role = $this->applyContext( $entity, 'loadable' );
        // form1
        if( $this->checkState( 'form1' ) ) {
            $this->nextState();
            return $view->showForm1( $entity );
        }
        $role->load( 'form1' );
        $role->verify( 'form1' );

        // form2
        $view->showForm2( $entity );
        $role->load( 'form2' );
        $role->verify( 'form2' );

        // confirm
        $view->showConfirm( $entity );

        // save
        $role = $this->applyContext( $entity, 'active' );
        $role->insert();

        // done
        $view->showDone( $entity );
    }
    /**
     * @param string $control
     * @param view $view
     * @return \view
     */
    function entityAdd_3rd( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        if( !$entity ) {
            $entity = $this->contextGet( 'entity' );
            $this->register( 'entity', $entity );
            $this->setState( [ 'form1', 'form2', 'confirm', 'save', 'done' ] );
        }
        $role = $this->applyContext( $entity, 'loadable' );
        // form1
        if( $this->checkState( 'form1' ) ) {
            $this->nextState();
            return $view->showForm1( $entity );
        }
        if( $control == 'form1' ) {
            $role->load( 'form1' );
        }
        $ok = $role->verify( 'form1' );
        if( !$ok ) {
            return $view->showForm1( $entity );
        }

        // form2
        if( $this->checkState( 'form2' ) ) {
            $this->nextState();
            return $view->showForm2( $entity );
        }
        $role->load( 'form2' );
        $role->verify( 'form2' );

        // confirm
        $view->showConfirm( $entity );

        // save
        $role = $this->applyContext( $entity, 'active' );
        $role->insert();

        // done
        $view->showDone( $entity );
    }
    /**
     * @param string $control
     * @param view $view
     * @return \view
     */
    function entityAdd( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        if( !$entity ) {
            $entity = $this->contextGet( 'entity' );
            $this->register( 'entity', $entity );
            $this->setState( [ 'form1', 'form2', 'confirm', 'save', 'done' ] );
        }
        $role = $this->applyContext( $entity, 'loadable' );
        // form1
        if( $this->checkState( 'form1' ) ) {
            $this->nextState();
            return $view->showForm1( $entity );
        }
        if( $control == 'form1' ) {
            $role->load( 'form1' );
        }
        $ok = $role->verify( 'form1' );
        if( !$ok ) {
            return $view->showForm1( $entity );
        }

        // form2
        if( $this->checkState( 'form2' ) ) {
            $this->nextState();
            return $view->showForm2( $entity );
        }
        if( $control == 'form2' ) {
            $role->load( 'form2' );
        }
        $ok = $role->verify( 'form2' );
        if( !$ok ) {
            return $view->showForm2( $entity );
        }
        // confirm
        // TODO: form1に戻った場合に処理が続かない。
        if( $this->checkState( 'confirm' ) ) {
            $this->nextState();
            return $view->showConfirm( $entity );
        }

        // save
        if( $this->checkState( 'save' ) ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
            $this->nextState();
        }

        // done
        $view->showDone( $entity );
    }
    /**
     * @param string $control
     * @param view $view
     * @return \view
     */
    function entityAdd_good( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        if( !$entity ) {
            $entity = $this->contextGet( 'entity' );
            $this->register( 'entity', $entity );
            $this->setState( [ 'form1', 'form2', 'confirm', 'save', 'done' ] );
        }
        $role = $this->applyContext( $entity, 'loadable' );
        // form1
        if( $this->execStateOrControl( $control, 'form1' ) ) {
            return $view->showForm1( $entity );
        }
        if( $control == 'load1' ) {
            $role->load( 'load1' );
        }
        if( !$role->verify( 'load1' ) ) {
            return $view->showForm1( $entity );
        }
        // form2
        if( $this->execStateOrControl( $control, 'form2' ) ) {
            return $view->showForm2( $entity );
        }
        if( $control == 'load2' ) {
            $role->load( 'load2' );
        }
        if( !$role->verify( 'load2' ) ) {
            return $view->showForm2( $entity );
        }
        // confirm
        if( $this->checkState( $control, 'confirm' ) ) {
            if( $control == 'save' ) {
                $this->nextState();
            }
            else {
                return $view->showConfirm( $entity );
            }
        }
        // save
        if( $this->checkState( 'save' ) ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
            $this->nextState();
        }
        // done
        $view->showDone( $entity );
    }
}

class entity extends Interaction
{
    function entityAdd( $control, $view )
    {
        if( $role = $this->restore( 'role' ) ) {
            $role = $this->context->getActiveRole( 'entity' );
            $this->register( 'role', $role );
        }
        if( $control == 'form' ) {
            return $view->showForm( $role );
        }
        if( $control == 'confirm' ) {
            $role->loadInput();
        }
        $ok = $role->verify();
        if( !$ok || $control == 'confirm' ) {
            if( $ok ) $view->setToken( $this->setToken() );
            return $view->showConfirm( $role );
        }
        if( $ok && $control == 'insert' && $this->tokenOk() ) {
            $role->insert();
            return $view->showReloadDone( $role );
        }
        $role->getFromIdInSession();
        return $view->showDone( $role );
    }
    function BareEntityAdd( $control, $view )
    {
        if( $role = $this->restore( 'role' ) ) {
            $role = $this->context->getActiveRole( 'entity' );
            $this->register( 'role', $role );
        }
        if( $control == 'form' ) {
            return $view->showForm( $role );
        }
        $role->loadInput();
        $ok = $role->verify();
        if( $ok ) {
            $role->insert();
            return $view->showReloadDone( $role );
        }
        $role->getFromIdInSession();
        return $view->showDone( $role );
    }
}

class entityCases extends Reinvocation
{
    function entityAdd( $control, $view )
    {
        if( $role = $this->restore( 'role' ) ) {
            $role = $this->context->getActiveRole( 'entity' );
            $this->register( 'role', $role );
        }
        switch( $control ) {
            case 'form':
                return $view->showForm( $role );
            case 'confirm':
                if( !$role->verifyInput() ) {
                    return $view->showForm( $role );
                }
                $view->setToken( $this->setToken() );
                return $view->showConfirm( $role );
            case 'insert':
                if( !$role->verifyInput() ) {
                    return $view->showForm( $role );
                }
                if( $this->tokenOk() ) {
                    $role->insert();
                    return $view->showReloadDone( $role );
                }
            default:
                $role->getFromIdInSession();
                return $view->showDone( $role );
                break;
        }
    }
}


function action_add( $action, $ctrl, $view )
{
    $role = $ctrl->getRole();
    if( $action == 'form' ) {
        return $view->showForm( $role );
    }
    $ok = $ctrl->verifyInput( $role );
    if( !$ok ) {
        return $view->showForm( $role );
    }
    if( $action == 'confirm' ) {
        $view->setToken( $ctrl->getToken() );
        return $view->showConfirm( $role );
    }
    if( $action == 'insert' && $ctrl->tokenOk() ) {
        $role->insert();
        $role->vanish();
    }
    return $view->showInform( $role );
}

function add_bare( $action, $ctrl, $view )
{
    $role = $ctrl->getRole();
    if( $action == 'form' ) {
        return $view->showForm( $role );
    }
    $ok = $ctrl->verifyInput( $role );
    if( $ok && $ctrl->tokenOk() ) {
        $role->insert();
        $role->vanish();
    }
    return $view->showInform( $role );
}
