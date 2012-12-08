<?php
namespace wsModule\Alt\Web;

/**
 * Interaction for web input and output, such as forms.
 * This is a DCI inspired module. things works as coded but still quite experimental.
 *
 * TODO: remove $this->view property. Interaction should not know about view...
 */
class Interaction
{
    /** @var array                          data to register as session data */
    protected $registeredData = array();

    /** @var \wsModule\Alt\Web\Request */
    protected $request;
    
    /** @var \WScore\Web\Session             saves itself and token for CSRF */
    protected $session;

    /** @var \WScore\DataMapper\Role */
    protected $role;

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
     * @param \WScore\DataMapper\Role $role
     * @DimInjection Get   \wsModule\Alt\Web\Request
     * @DimInjection Fresh Session
     * @DimInjection Get   \WScore\DataMapper\Role
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
     */
    public function run( $controller, $action )
    {
        $this->$controller( $action );
        $this->saveRegistered();
    }

    /**
     * @param string      $controller
     * @param null|string $default
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
     * @param \WScore\DataMapper\Entity_Interface $entity
     * @param string                            $action
     * @param string                            $form
     * @param string|null                       $prevForm
     * @return bool
     */
    public function contextFormAndLoad( $entity, $action, $form, $prevForm=null )
    {
        $role     = $this->role->applyLoadable( $entity );
        $isPost   = $this->request->isPost();
        // show form at least once. check for pin-point. 
        if ( !$this->checkPin( $form ) ) {
            // no validation result is necessary when showing the form.
            $role->resetValidation( true );
            return true;
        }
        // requesting for a form. 
        if ( $action == $prevForm || ( $action == $form && !$isPost ) ) {
            // no validation result is necessary when showing the form.
            $role->resetValidation( true );
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
     * @param \WScore\DataMapper\Entity_Interface $entity
     * @param string                            $action
     * @param string                            $form
     * @return bool
     */
    public function contextValidateUnless( $entity, $action, $form )
    {
        $role = $this->role->applyLoadable( $entity );
        // validate data *always*. 
        if ( !$role->validate() ) {
            return true;
        }
        if( $action != $form ) {
            return true;
        }
        return false;
    }

    /**
     * saves the entity if $action is $form and token is verified. pin points the $form.
     * returns true if $action is in this context (i.e. entity is saved), otherwise
     * returns false.
     * 
     * @param \WScore\DataMapper\Entity_Interface $entity
     * @param string                            $action
     * @param string                            $form
     * @return bool
     */
    public function contextSaveOnlyOnce( $entity, $action, $form )
    {
        // check if already saved.
        if( $this->checkPin( $form ) ) return false;
        // it's new. and further check the token.
        if( $action == $form )
        {
            $role = $this->role->applyActive( $entity );
            $role->save();
            $this->pinPoint( $form );
            return true;
        }
        return false;
    }

    // +----------------------------------------------------------------------+
    //  wizards and helpers for using context.
    // +----------------------------------------------------------------------+
    /**
     * shows form, load and validates input data.
     * returns the form name if action is in this context, and false if not.
     *
     * @param \WScore\DataMapper\Entity_Interface $entity
     * @param string $action
     * @param array  $forms
     * @return bool|string
     */
    public function formWizards( $entity, $action, $forms )
    {
        $prevForm = null;
        foreach( $forms as $form ) {
            if( $this->contextFormAndLoad( $entity, $action, $form, $prevForm ) ) {
                return $form;
            }
            $prevForm = $form;
        }
        return false;
    }
    // +----------------------------------------------------------------------+
}
