<?php
namespace dci;

class Interaction
{
    /** @var array                          data to register as session data */
    protected $registeredData = array();

    /** @var \wsCore\Web\Session             saves itself and token for CSRF */
    protected $session;
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     */
    public function __construct() {
    }

    /**
     * @param \wsCore\Web\Session $session
     */
    public function setSession( $session ) {
        $this->session = ($session) ?: $_SESSION;
    }

    /**
     * load itself from session
     * @param \wsCore\Web\Session $session
     * @return mixed
     */
    public static function load( $session ) {
        $class = self::saveName( get_called_class() );
        if( $src = $session->get( $class ) ) {
            $object = unserialize( $src );
        }
        else {
            $object = new static();
        }
        $object->setSession( $session );
        return $object;
    }

    /**
     * saves the instance to session.
     */
    public function save() {
        $class = self::saveName( get_called_class() );
        $this->session->set( $class, serialize( $this ) );
    }

    /**
     * @param $class
     * @return mixed
     */
    protected static function saveName( $class ) {
        $class = str_replace( '\\', '__', $class );
        return $class;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param view $view
     * @return view
     */
    public function run( $controller, $action, $view )
    {
        $view = $this->$controller( $action, $view );
        $this->save();
        return $view;
    }
    // +----------------------------------------------------------------------+
    //  manage token for CSRF.
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    public function makeToken() {
        return $this->session->pushToken();
    }

    /**
     * @return bool
     */
    public function verifyToken() {
        return $this->session->verifyToken();
    }
    // +----------------------------------------------------------------------+
    //  manage variables
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @param mixed $data
     */
    public function registerData( $name, $data ) {
        $this->registeredData[ $name ] = $data;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function restoreData( $name ) {
        return $this->registeredData[ $name ];
    }

    /**
     * clears registered data
     */
    public function clearData() {
        $this->registeredData = array();
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

    /**
     * @param string $action
     * @param \dci\view $view
     * @return \dci\view
     */
    function addEntity( $action, $view )
    {
        // get entity
        $this->view = $view;
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->contextGet( 'entity' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        elseif( $this->restoreData( 'complete' ) ) {
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
    function actionFormAndLoad( $entity, $action, $form, $load )
    {
        $role = $this->applyContext( $entity, 'loadable' );
        // check if this form has shown before.
        $pinpoint = '_pin_' . $form;
        if( !$this->restoreData( $pinpoint ) ) {
            $this->registerData( $pinpoint, true );
            $this->view->showForm( $entity, $form );
            return true;
        }
        // show the form.
        if( $action == $form ) {
            $this->view->showForm( $entity, $form );
            return true;
        }
        // load posted values from form.
        if( $action == $load ) {
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

