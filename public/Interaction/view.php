<?php
namespace Interaction;

class view extends \wsCore\Html\PageView
{
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
        $this->set( 'title', 'Friend Form' );
        $this->set( 'button-primary', 'confirm information' );
        $this->set( 'button-sub', 'reset' );
    }
    public function showForm_wizard1( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load1' );
        $this->set( 'button-primary', 'next' );
        $this->set( 'button-sub', '' );
    }
    public function showForm_wizard2( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load2' );
        $this->set( 'button-primary', 'next' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard1' );
    }
    public function showForm_wizard3( $entity )
    {
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load3' );
        $this->set( 'button-primary', 'confirm inputs' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard2' );
    }
    public function showConfirm( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'currAction', 'confirm' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Confirmation of Inputs' );
        $this->set( 'action', 'save' );
        $this->set( 'button-primary', 'save the information' );
        $this->set( 'button-sub', 'interaction2.php?action=wizard3' );
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
        return $this->bootstrapAlert( 'alert-success', $message, $title );
    }

    public function bootstrapAlertInfo() {
        $message = $this->get( 'alert-info' );
        if( !$message ) return '';
        $title   = 'Notice:';
        return $this->bootstrapAlert( 'alert-info', $message, $title );
    }

    public function bootstrapAlertError() {
        $message = $this->get( 'alert-error' );
        if( !$message ) return '';
        $title   = 'Error Message:';
        return $this->bootstrapAlert( 'alert-error', $message, $title );
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

    public function bootstrapButtonPrimary( $name, $loading='' ) {
        $title = $this->get( $name );
        if( !$title ) return '';
        return $this->bootstrapButton( 'submit', $title, 'primary', $loading );
    }

    public function bootstrapButtonSub( $name ) {
        $type = $this->get( $name );
        if( !$type ) return '';
        if( $type == 'reset' )     { $title = 'reset form'; }
        elseif( $type == 'back' )  { $title = 'back'; }
        else {
            $title = $type;
            $type  = 'submit';
        }
        return $this->bootstrapButton( $type, $title, '' );
    }

    public function bootstrapButtonJump( $name, $title='Go Back' ) {
        $href = $this->get( $name );
        if( !$href ) return '';
        $html = "<button type=\"button\" class=\"but\" onclick=\"location.href='{$href}'\">{$title}</button>";
        return $html;
    }

    public function bootstrapButton( $type, $title, $class, $loading='' ) {
        $extra = '';
        if( $loading ) $extra .= " data-loading-text=\"{$loading}\"";
        if( $type == 'back' ) {
            $extra .= ' onClick="history.back();"';
            $type = 'button';
        }
        if( $type == 'reset' ) {
            $extra .= ' onClick="return window.confirm( \'reset the inputs?\');"';
        }
        $html = "<button type=\"{$type}\" class=\"but btn-{$class}\" {$extra} >{$title}</button>";
        return $html;
    }
}

