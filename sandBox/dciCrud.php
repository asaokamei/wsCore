<?php


function action_add( $action, $ctrl, $view )
{
    $role = $ctrl->getRole();
    if( $action == 'form' ) {
        return $view->showForm( $role );
    }
    $ok = $ctrl->verifyInput( $role );
    if( !$ok ) {
        $view->setToken( $ctrl->getToken() );
        return $view->showForm( $role );
    }
    if( $action == 'confirm' ) {
        return $view->showConfirm( $role );
    }
    if( $action == 'insert' && $ctrl->tokenOk() ) {
        $role->insert();
        $role->vanish();
    }
    return $view->showInform( $role );
}

