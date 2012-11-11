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
    
    public $showForm = 'showForm';

    public $loadData = 'loadData';
    
    public $actionName = 'action';
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\Web\Session $session
     * @param \wsCore\DbAccess\Context $context
     * @DimInjection Fresh Session
     * @DimInjection Get   \wsCore\DbAccess\Context
     */
    public function __construct( $session, $context ) {
        $this->session = ($session) ?: $_SESSION;
        $this->context = $context;
    }

    /**
     * load itself from session
     *
     * @throws \RuntimeException
     * @return mixed
     */
    public function loadRegistered() {
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
     * @param \wsCore\Html\PageView $view
     * @return \wsCore\Html\PageView
     */
    public function run( $controller, $action, $view )
    {
        $view = $this->$controller( $action, $view );
        $this->saveRegistered();
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
        $entity = new \stdClass();
        $entity->entityName = $entityName;
        $entity->_actions[] = 'created';
        return $entity;
    }
    // +----------------------------------------------------------------------+

    /**
     * @param \wsCore\Html\PageView               $view
     * @param \wsCore\DbAccess\Context_RoleInput  $role
     * @param string $action
     * @param string $form
     * @param string $load
     * @return bool
     */
    function actionFormAndLoad( $view, $role, $action, $form, $load )
    {
        // check if this form has shown before.
        if( strpos( $form, '|' ) !== false ) {
            $formList = explode( '|', $form );
            $form = $formList[0];
        }
        else {
            $formList = array( $form );
        }
        /** @var $formList array  */

        $pinpoint = '_pin_' . $form;
        if( !$this->restoreData( $pinpoint ) ) {
            $this->registerData( $pinpoint, true );
            $role->resetValidation( true );
            $showForm = $this->showForm;
            $view->$showForm( $role, $form );
            return true;
        }
        // show the form.
        if( in_array( $action, $formList ) ) {
            $showForm = $this->showForm;
            $view->$showForm( $role, $form );
            return true;
        }
        // load posted values from form.
        if( $load && $action == $load ) {
            $loadData = $this->loadData;
            $role->$loadData( $load );
        }
        // always verify the input.
        if( !$role->validate( $form ) ) {
            $showForm = $this->showForm;
            $view->$showForm( $role, $form ); // validation failed.
            return true;
        }
        return false;
    }
    
    private function getStepInfo( $step ) {
        $formName = $step[0];
        $loadName = $step[1];
        $token    = (isset( $step[2] ))? $step[2] : null;
        return array( $formName, $loadName, $token );
    }

    /**
     * generic form and load steps for web-interaction.
     * returns the entity if all the steps are successful, otherwise
     * returns just false.
     *
     * $steps = array(
     *    [ 'formName',    'loadName',   null     ],
     *    [ 'formName2',   'loadName2'   null     ],
     *    ...
     *    [ 'confirmName', 'finalAction', 'push'    ],
     *    [ 'finalAction', 'doneName',    'verify'  ],
     * );
     *
     * about return value: boolen or Entity_Interface.
     * returns true if view is taken care of. 
     * 
     * @param \wsCore\Html\PageView             $view
     * @param \wsCore\DbAccess\Entity_Interface $entity
     * @param string                            $action
     * @param array                             $steps
     * @return bool|\wsCore\DbAccess\Entity_Interface
     */
    public function webFormWizard( $view, $entity, $action, $steps )
    {
        $role = $this->context->applyLoadable( $entity );
        $showForm = $this->showForm;
        $lastStep = array_pop( $steps );
        list( $formName, $loadName, $token ) = $this->getStepInfo( $lastStep );
        if( $this->restoreData( $loadName ) ) {
            return false;
        }
        if( $action == $formName ) {
            $this->registerData( $loadName, true );
            if( $token == 'verify' && !$this->verifyToken() ) {
                return false;
            }
            return $entity;
        }
        
        reset( $steps );
        $prevLoadName = null;
        foreach( $steps as $step ) 
        {
            list( $formName, $loadName, $token ) = $this->getStepInfo( $step );
            $view->set( $this->actionName, $loadName );
            if( $action == $formName && $token == 'push' ) {
                $view->set( $this->session->popTokenTagName(), $this->session->pushToken() );
                $view->$showForm( $role, $formName );
                return true;
            }
            $formName .= $prevLoadName ? '|' . $prevLoadName: '';
            if( $this->actionFormAndLoad( $view, $role, $action, $formName, $loadName ) ) return true;
            $prevLoadName = $loadName;
        }
        return false;
    }
    // +----------------------------------------------------------------------+
}
