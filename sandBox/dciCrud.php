<?php


class entityManyForms extends Reinvocation
{
    function entityAdd( $control, $view )
    {
        if( $role = $this->restore( 'role' ) ) {
            $role = $this->context->getActiveRole( 'entity' );
            $this->register( 'role', $role );
        }
        if( $control == 'form1' ) {
            return $view->showForm1( $role );
        }
        if( $control == 'form2' ) {
            $role->loadInput1();
            if( !$role->verify1() ) {
                return $view->showForm1( $role );
            }
            return $view->showForm2( $role );
        }
        if( $control == 'confirm' ) {
            $role->loadInput2();
            if( !$role->verify2() ) {
                return $view->showForm2( $role );
            }
        }
        $ok = $role->verify();
        if( !$ok || $control == 'confirm' ) {
            if( $ok ) $view->setToken( $this->setToken() );
            return $view->showConfirm( $role );
        }
        if( $control == 'insert' && $this->tokenOk() ) {
            $role->insert();
            return $view->showReloadDone( $role );
        }
        $role->getFromIdInSession();
        return $view->showDone( $role );
    }
}

class entity extends Reinvocation
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
