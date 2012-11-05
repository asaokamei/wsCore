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
        return array_splice( $this->states, 0, 1 );
    }
    public function nextStateIf( $state ) {
        if( $state == $this->getState() ) {
            return array_splice( $this->states, 0, 1 );
        }
        return null;
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
    /**
     * @param $entity
     * @param $role
     * @return \role
     */
    public function applyContext( $entity, $role ) {
        return $entity;
    }

    /**
     * @param $entityName
     * @return mixed
     */
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

class role
{
    function load( $name=null ) { return true; }
    function verify( $name=null ) { return true; }
    function insert() { return true; }
}
/*

        ctrl    state
form1     x       x
load1     x
form2     x       x   
load2     x
confirm   x       x
save              x
done      x       x


 */

class stateEntity extends Interaction
{
    /**
     * @param string $control
     * @param \view $view
     * @return mixed
     */
    function entity_do( $control, $view )
    {
        $state  = $this->getState();
        $entity = $this->restore( 'entity' );
        if( !$state ) {
            $entity = $this->contextGet( 'entity' );
            $this->register( 'entity', $entity );
            $this->setState( [ 'form1', 'form2', 'confirmed', 'ready', 'done' ] );
            $state  = $this->getState();
        }
        $method = 'state_' . $state;
        return $this->$method( $control, $entity, $view );
    }

    /**
     * @param $control
     * @param $entity
     * @param \view $view
     * @return mixed
     */
    function state_form1( $control, $entity, $view )
    {
        $this->nextStateIf( 'form1' );
        return $view->showForm1( $entity );
    }

    /**
     * @param string $entity
     * @return bool
     */
    function load1_entity( $entity )
    {
        $role = $this->applyContext( $entity, 'loadable' );
        return $role->verify( 'load1' );
    }

    /**
     * @param $control
     * @param $entity
     * @param \view $view
     * @return \view
     */
    function state_form2( $control, $entity, $view )
    {
        if( $control == 'form1' ) {
            return $this->state_form1( $control, $entity, $view );
        }
        $this->nextStateIf( 'form2' );
        if( $this->load1_entity( $entity ) ) return $view->showForm1( $entity );
        return $view->showForm2( $entity );
    }

    /**
     * @param string $entity
     * @return bool
     */
    function load2_entity( $entity )
    {
        $role = $this->applyContext( $entity, 'loadable' );
        return $role->verify( 'load2' );
    }

    /**
     * @param $control
     * @param $entity
     * @param \view $view
     * @return \view
     */
    function state_confirmed( $control, $entity, $view )
    {
        if( in_array( $control, [ 'form1', 'form2' ] ) ) {
            return $this->state_form2( $control, $entity, $view );
        }
        elseif( $control == 'load1' ) {
            if( $this->load1_entity( $entity ) ) return $view->showForm2( $entity );;
        }
        elseif( $control == 'load2' ) {
        if( $this->load2_entity( $entity ) ) return $view->showForm2( $entity );;
    }
        $role = $this->applyContext( $entity, 'loadable' );
        // load2
        if( $role->verify() ) $this->nextStateIf( 'confirmed' );

        return $view->showConfirm( $entity );
    }

    /**
     * @param $control
     * @param $entity
     * @param \view $view
     * @return \view
     */
    function state_ready( $control, $entity, $view )
    {
        if( $control == 'save' ) {
            $this->nextState();
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
            return $view->showDone( $entity );
        }
        else {
            return $this->state_confirmed( $control, $entity, $view );
        }
    }

    /**
     * @param $control
     * @param $entity
     * @param \view $view
     * @return \view
     */
    function state_done( $control, $entity, $view ) {
        return $view->showDone( $entity );
    }
}

class controlEntity extends Interaction
{
    /**
     * @param string $control
     * @param view $view
     * @return \view
     */
    function entityAdd_bare( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        $role = $this->applyContext( $entity, 'loadable' );

        // form1
        $role->verify( 'load1' );

        // form2
        $role->verify( 'load2' );

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
    function entityAdd_forms( $control, $view )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        $role = $this->applyContext( $entity, 'loadable' );

        // form1
        $view->showForm1( $entity );
        // load1
        $role->load( 'load1' );

        $role->verify( 'load1' );

        // form2
        $view->showForm2( $entity );
        // load2
        $role->load( 'load2' );

        $role->verify( 'load2' );

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
        $state  = $this->getState();
        if( !$state ) {
            $entity = $this->contextGet( 'entity' );
            $this->register( 'entity', $entity );
            $this->setState( [ 'form1', 'form2', 'confirm', 'save', 'done' ] );
        }
        $role = $this->applyContext( $entity, 'loadable' );
        // form1
        if( $control == 'form1' || $state == 'form1' ) {
            $this->nextStateIf( 'form1' );
            return $view->showForm1( $entity );
        }
        // load1
        if( $control == 'load1' ) $role->load( 'load1' );
        
        if( !$role->verify( 'load1' ) ) return $view->showForm1( $entity );

        // form2
        if( $control == 'form2' || $state == 'form2' ) {
            $this->nextStateIf( 'form2' );
            return $view->showForm2( $entity );
        }
        // load2
        if( $control == 'load2' ) $role->load( 'load2' );
        
        if( !$role->verify( 'load2' ) ) return $view->showForm1( $entity );

        if( $control == 'save' && $state == 'confirm' ) $this->nextState();
        
        // confirm
        if( $state == 'confirm' ) {
            return $view->showConfirm( $entity );
        }
        
        // save
        if( $state == 'save' ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
            $this->nextState();
        }

        // done
        return $view->showDone( $entity );
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
        if( $control == 'form2' ) $role->load( 'load1' );
        if( !$role->verify( 'load1' ) ) return $view->showForm1( $entity );

        // form2
        if( $control == 'form2' ) {
            return $view->showForm2( $entity );
        }
        if( $control == 'confirm' ) $role->load( 'load2' );
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
