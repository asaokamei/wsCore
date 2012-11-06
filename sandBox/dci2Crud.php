<?php
namespace dci;

class Interaction
{
    protected $variables = array();

    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    public function __construct() {
    }
    public static function load() {
        $class = self::saveName( get_called_class() );
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
    //  manage token for CSRF.
    // +----------------------------------------------------------------------+
    public function makeToken() {
        return 'token';
    }
    public function verifyToken() {
        return true;
    }
    public function isMethodGet() {
        return true;
    }
    public function isMethodPost() {
        return true;
    }
    // +----------------------------------------------------------------------+
    //  manage variables
    // +----------------------------------------------------------------------+
    public function register( $name, $data ) {
        $this->variables[ $name ] = $data;
    }
    public function restore( $name ) {
        return $this->variables[ $name ];
    }
    public function clear() {
    }
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
    function showForm( $entity, $formName=null ) { return $this; }
    function showForm1() { return $this; }
    function showForm2() { return $this; }
    function showConfirm() { return $this; }
    function showDone() { return $this; }
    function setToken( $token ) {}

}

class role
{
    function load( $name=null ) { return true; }
    function verify( $name=null ) { return true; }
    function insert() { return true; }
}

class ControllerCrud extends Interaction
{
    /** @var view */
    protected $view;

    function setView( $view ) {
        $this->view = $view;
    }

    /**
     * @param string $action
     * @return \dci\view
     */
    function addEntity( $action )
    {
        // get entity
        $entity = $this->restore( 'entity' );
        if( !$entity ) {
            $entity = $this->contextGet( 'entity' );
            $this->clear();
            $this->register( 'entity', $entity );
        }
        elseif( $this->restore( 'complete' ) ) {
            goto done;
        }
        if( $this->actionFormAndLoad( $entity, $action, 'form1', 'load1' ) ) return $this->view;
        if( $this->actionFormAndLoad( $entity, $action, 'form2', 'load2' ) ) return $this->view;

        // show confirm except for save.
        if( $action != 'save' ) {
            $this->view->setToken( $this->makeToken() );
            return $this->view->showConfirm( $entity );
        }
        // save entity.
        if( $action == 'save' && $this->verifyToken() ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
        }
        // done
        done :
        return $this->view->showDone( $entity );
    }

    /**
     * @param $entity
     * @param string $action
     * @param string $form
     * @param string $load
     * @return bool
     */
    function actionFormAndLoad( $entity, $action, $form, $load=null )
    {
        if( !$load ) $load = $form;
        $role = $this->applyContext( $entity, 'loadable' );
        // check if this form has shown before.
        if( !$this->restore( $form ) ) {
            $this->register( $form, true );
            $this->view->showForm( $entity, $form );
            return true;
        }
        // show the form.
        if( $action == $form && $this->isMethodGet() ) {
            $this->view->showForm( $entity, $form );
            return true;
        }
        // load posted values from form.
        if( $action == $load && $this->isMethodPost() ) {
            $role->load( $load );
        }
        // always verify the input.
        if( !$role->verify( $load ) ) {
            $this->view->showForm( $entity, $form ); // validation failed.
            return true;
        }
        return false;
    }
}

