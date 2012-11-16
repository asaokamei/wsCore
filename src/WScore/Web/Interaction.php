<?php
namespace WScore\Web;

class Interaction
{
    /** @var array                          data to register as session data */
    protected $registeredData = array();

    /** @var \WScore\Web\Session             saves itself and token for CSRF */
    protected $session;

    /** @var \WScore\DbAccess\Role */
    protected $role;
    
    /** @var \WScore\Html\PageView */
    protected $view;
    
    public $showForm = 'showForm';

    public $loadData = 'loadData';
    
    public $actionName = 'action';
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Web\Session $session
     * @param \WScore\DbAccess\Role $role
     * @DimInjection Fresh Session
     * @DimInjection Get   \WScore\DbAccess\Role
     */
    public function __construct( $session, $role ) {
        $this->session = ($session) ?: $_SESSION;
        $this->role = $role;
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
    // +----------------------------------------------------------------------+

    /**
     * @param \WScore\DbAccess\Role_Input  $role
     * @param string $action
     * @param array $formList
     * @param string $load
     * @return bool
     */
    function actionFormAndLoad( $role, $action, $formList, $load )
    {
        $form = $formList[0];
        $pinpoint = '_pin_' . $form;
        // show formName at least once. 
        if( !$this->restoreData( $pinpoint ) )  // formName not pin-pointed. show the form.
        {
            $this->registerData( $pinpoint, true ); // pin point. 
            $role->resetValidation( true );
            $showForm = $this->showForm;
            $this->view->$showForm( $role, $form );
            return true;
        }
        // action to show the form. either the formName, or previous loadName. 
        if( in_array( $action, $formList ) ) {
            $showForm = $this->showForm;
            $this->view->$showForm( $role, $form );
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
            $this->view->$showForm( $role, $form ); // validation failed.
            return true;
        }
        return false;
    }

    /**
     * @param array $step
     * @return array
     */
    private function getStepInfo( $step ) {
        $task     = $step[0];
        $formName = $step[1];
        $loadName = array_key_exists( 2, $step ) ? $step[2] : null;
        return array( $task, $formName, $loadName );
    }

    /**
     * generic web-interaction based on steps.
     *
     * $steps = array(
     *    [  taskType,      formName,      loadName ], 
     *    [ 'formLoad',    'formName',    'loadName'    ],
     *    [ 'formLoad',    'formName2',   'loadName2'   ],
     *    ...
     *    [ 'pushToken',   'confirmName' ],
     *    [ 'verifyToken', 'finalAction', 'doneName'    ],
     * );
     * 
     * available taskTypes are: formLoad, pushToken, and verifyToken.
     * 
     * about return value: formName or false.
     * returns formName if task was successfully performed. 
     * returns false if no task was performed (no action for this steps), 
     * or failed to perform the task in verifyToken. 
     * 
     * @param \WScore\DbAccess\Entity_Interface $entity
     * @param string                            $action
     * @param array                             $steps
     * @return bool|string
     */
    public function webFormWizard( $entity, $action, $steps )
    {
        $role = $this->role->applyInputAndSelectable( $entity );
        $showForm = $this->showForm;
        $prevLoadName = null;
        foreach( $steps as $step ) 
        {
            list( $task, $formName, $loadName ) = $this->getStepInfo( $step );
            $this->view->set( $this->actionName, $loadName );
            if( $task == 'showData' && $action == $formName ) {
                $this->view->$showForm( $role, $formName );
                return $formName;
            }
            if( $task == 'pushToken' && in_array( $action, array( $formName, $prevLoadName ) ) ) {
                $this->pushToken();
                $this->view->$showForm( $role, $formName );
                return $formName;
            }
            if( $task == 'verifyToken' && $action == $formName ) {
                $doneName = $loadName ?: $formName;
                $this->registerData( $doneName, true );
                $this->view->$showForm( $role, $doneName );
                if( $this->verifyToken() ) {
                    return $formName;
                }
                return false;
            }
            if( $task == 'formLoad' ) {
                $formList = array( $formName, $prevLoadName );
                if( $this->actionFormAndLoad( $role, $action, $formList, $loadName ) ) return $formName;
            }
            $prevLoadName = $loadName;
        }
        return false;
    }
    // +----------------------------------------------------------------------+
}
