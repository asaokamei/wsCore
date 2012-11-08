<?php
namespace wsCore\Web;

class Interaction
{
    /** @var array                          data to register as session data */
    protected $registeredData = array();

    /** @var \wsCore\Web\Session             saves itself and token for CSRF */
    protected $session;

    /** @var \wsCore\DbAccess\Context */
    protected $context;
    
    protected $showForm = 'showForm';
    
    protected $loadData = 'loadData';
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
     * @param \wsCore\DbAccess\Context $context
     */
    public function setContext( $context ) {
        $this->context = $context;
    }
    /**
     * @param \wsCore\Web\Session $session
     * @return Interaction
     */
    public static function newInstance( $session ) {
        $class = self::getInstanceName( get_called_class() );
        $object = new static();
        $object->setSession( $session );
        return $object;
    }

    /**
     * load itself from session
     *
     * @param \wsCore\Web\Session $session
     * @throws \RuntimeException
     * @return mixed
     */
    public static function loadInstance( $session ) {
        $class = self::getInstanceName( get_called_class() );
        if( $src = $session->get( $class ) ) {
            $object = unserialize( $src );
            $object->setSession( $session );
            return $object;
        }
        throw new \RuntimeException( 'Object not saved: '.$class );
    }

    /**
     * saves the instance to session.
     */
    public function saveInstance() {
        $class = self::getInstanceName( get_called_class() );
        $this->session->set( $class, serialize( $this ) );
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
     * @param \wsCore\Html\PageView $view
     * @return \wsCore\Html\PageView
     */
    public function run( $controller, $action, $view )
    {
        $view = $this->$controller( $action, $view );
        $this->saveInstance();
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
        return \wsCore\Utilities\Tools::getKey( $this->registeredData, $name );
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
        $entity->role = $role;
        $entity->_actions[] = $role;
        return $entity;
    }

    /**
     * @param $entityName
     * @return mixed
     */
    public function contextGet( $entityName ) {
        $entity = new \interaction\entity();
        $entity->entityName = $entityName;
        $entity->_actions[] = 'created';
        return $entity;
    }
    // +----------------------------------------------------------------------+

    /**
     * @param \wsCore\Html\PageView              $view
     * @param \wsCore\DbAccess\Entity_Interface  $entity
     * @param string $action
     * @param string $form
     * @param string $load
     * @return bool
     */
    function actionFormAndLoad( $view, $entity, $action, $form, $load )
    {
        $role = $this->applyContext( $entity, 'loadable' );
        // check if this form has shown before.
        $pinpoint = '_pin_' . $form;
        if( !$this->restoreData( $pinpoint ) ) {
            $this->registerData( $pinpoint, TRUE );
            $showForm = $this->showForm;
            $view->$showForm( $entity, $form );
            return TRUE;
        }
        // show the form.
        if( $action == $form ) {
            $showForm = $this->showForm;
            $view->$showForm( $entity, $form );
            return TRUE;
        }
        // load posted values from form.
        if( $action == $load ) {
            $loadData = $this->loadData;
            $role->$loadData( $load );
        }
        // always verify the input.
        if( !$role->verify( $load ) ) {
            $showForm = $this->showForm;
            $view->$showForm( $entity, $form ); // validation failed.
            return TRUE;
        }
        return FALSE;
    }
    // +----------------------------------------------------------------------+
}
