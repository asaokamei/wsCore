<?php
namespace wsModule\Alt\Web;

class Interaction
{
    /** @var array                          data to register as session data */
    protected $registeredData = array();

    /** @var \wsModule\Alt\Web\Request */
    protected $request;
    
    /** @var \WScore\Web\Session             saves itself and token for CSRF */
    protected $session;

    /** @var \WScore\DbAccess\Role */
    protected $role;
    
    /** @var \WScore\Html\PageView */
    protected $view;
    
    public $showForm = 'showForm';

    public $loadData = 'loadData';
    
    public $actionName = 'action';
    
    public $action = null;
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \wsModule\Alt\Web\Request   $request
     * @param \WScore\Web\Session   $session
     * @param \WScore\DbAccess\Role $role
     * @DimInjection Get   \wsModule\Alt\Web\Request
     * @DimInjection Fresh Session
     * @DimInjection Get   \WScore\DbAccess\Role
     */
    public function __construct( $request, $session, $role )
    {
        $this->request = $request;
        $this->session = ($session) ?: $_SESSION;
        $this->role = $role;
    }

    /**
     * load itself from session
     *
     * @throws \RuntimeException
     * @return mixed
     */
    public function loadRegistered()
    {
        $class = self::getInstanceName( get_called_class() );
        if( $src = $this->session->get( $class ) ) {
            $object = unserialize( $src );
            $this->registeredData = $object;
            return $object;
        }
        throw new \RuntimeException( 'Object not saved: '.$class );
    }

    /**
     * saves the instance to session.
     */
    public function saveRegistered() {
        $class = self::getInstanceName( get_called_class() );
        $this->session->set( $class, serialize( $this->registeredData ) );
    }

    /**
     * @param $class
     * @return mixed
     */
    protected static function getInstanceName( $class ) {
        $class = str_replace( '\\', '__', $class );
        return $class;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param \WScore\Html\PageView $view
     * @return \WScore\Html\PageView
     */
    public function run( $controller, $action, $view )
    {
        $this->view = $view;
        $this->$controller( $action );
        $this->saveRegistered();
        return $view;
    }

    /**
     * @param string      $controller
     * @param null|string $default
     * @return \WScore\Html\PageView
     */
    public function action( $controller, $default=null )
    {
        $action = $default;
        if( array_key_exists( $this->actionName, $_REQUEST ) ) {
            $action = $_REQUEST[ $this->actionName ];
            $this->loadRegistered();
        }
        $this->$controller( $action );
        $this->saveRegistered();
        return $this->view;
    }
    // +----------------------------------------------------------------------+
    //  manage token for CSRF.
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    public function pushToken() {
        $token = $this->session->pushToken();
        $this->view->set( $this->session->popTokenTagName(), $token );
        return $token;
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
        return \WScore\Utilities\Tools::getKey( $this->registeredData, $name );
    }

    /**
     * clears registered data
     */
    public function clearData() {
        $this->registeredData = array();
    }

    public function pinPoint( $name ) {
        $pinPoint = '_pin_' . $name;
        $this->registerData( $pinPoint, true );
    }

    public function checkPin( $name )
    {
        $pinPoint = '_pin_' . $name;
        $pinned   = false;
        if( $this->restoreData( $pinPoint ) ) {
            $pinned = true;
        }
        $this->pinPoint( $name );
        return $pinned;
    }
    // +----------------------------------------------------------------------+
    //  contexts... a generic method for common use cases. 
    // +----------------------------------------------------------------------+
    /**
     * a context to show form and load post data from the form.
     * returns true if $action is in this context, otherwise 
     * returns false. 
     * 
     * todo: cannot catch validation failure. is it OK?
     *
     * @param \WScore\DbAccess\Entity_Interface $entity
     * @param string                            $action
     * @param string                            $form
     * @return bool
     */
    public function contextFormAndLoad( $entity, $action, $form )
    {
        $role     = $this->role->applyLoadable( $entity );
        $isPost   = $this->request->isPost();
        // show form at least once. check for pin-point. 
        if ( !$this->checkPin( $form ) ) {
            $role->resetValidation( true );
            return true;
        }
        // requesting for a form. 
        if ( $action == $form && !$isPost ) {
            return true;
        }
        // load data if it is a post for a form. 
        if ( $action == $form && $isPost ) {
            $loadData = $this->loadData;
            $role->$loadData( $form );
        }
        // validate data *always*. 
        if ( !$role->validate( $form ) ) {
            return true;
        }
        // all pass. not in this context. 
        return false;
    }

    /**
     * for mostly showing confirm view. validates, again, and pushes token.
     * returns true if $action is in this context, otherwise
     * returns false.
     * 
     * @param \WScore\DbAccess\Entity_Interface $entity
     * @param string                            $action
     * @param string                            $form
     * @return bool
     */
    public function contextValidateAndPushToken( $entity, $action, $form )
    {
        $role = $this->role->applyLoadable( $entity );
        // validate data *always*. 
        if ( !$role->validate() ) {
            return true;
        }
        if( $action != $form ) {
            $this->pushToken();
            return true;
        }
        return false;
    }

    /**
     * saves the entity if $action is $form and token is verified. pin points the $form.
     * returns true if $action is in this context (i.e. entity is saved), otherwise
     * returns false.
     * 
     * @param \WScore\DbAccess\Entity_Interface $entity
     * @param string                            $action
     * @param string                            $form
     * @return bool
     */
    public function contextVerifyTokenAndSave( $entity, $action, $form )
    {
        // check if already saved.
        if( $this->checkPin( $form ) ) return false;
        // it's new. and further check the token.
        if( $action == $form && $this->verifyToken() )
        {
            $role = $this->role->applyActive( $entity );
            $role->save();
            $this->pinPoint( $form );
            return true;
        }
        return false;
    }
    // +----------------------------------------------------------------------+
}
