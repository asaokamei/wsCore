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
     * @param \wsCore\Html\PageView             $view
     * @param \wsCore\DbAccess\Entity_Interface $entity
     * @param string                            $action
     * @param array                             $steps
     * @return bool|string
     */
    public function webFormWizard( $view, $entity, $action, $steps )
    {
        $role = $this->context->applyLoadable( $entity );
        $showForm = $this->showForm;
        
        $prevLoadName = null;
        foreach( $steps as $step ) 
        {
            list( $task, $formName, $loadName ) = $this->getStepInfo( $step );
            $view->set( $this->actionName, $loadName );
            if( $task == 'pushToken' && in_array( $action, array( $formName, $prevLoadName ) ) ) {
                $view->set( $this->session->popTokenTagName(), $this->session->pushToken() );
                $view->$showForm( $role, $formName );
                return $formName;
            }
            if( $task == 'verifyToken' && $action == $formName ) {
                $view->$showForm( $role, $loadName );
                if( $this->verifyToken() ) {
                    return $formName;
                }
                return false;
            }
            if( $task == 'formLoad' ) {
                $formName .= $prevLoadName ? '|' . $prevLoadName: '';
                if( $this->actionFormAndLoad( $view, $role, $action, $formName, $loadName ) ) return $formName;
            }
            $prevLoadName = $loadName;
        }
        return false;
    }
    // +----------------------------------------------------------------------+
}
