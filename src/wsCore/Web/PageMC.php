<?php
namespace wsCore\Web;

class PageMcException extends \Exception {}

class PageMC
{
    const ERR_NONE       =     0;
    const ERR_NOTICE     =    10;
    const ERR_WARNING    =    50;
    const ERR_ERROR      =   100;
    const ERR_FATAL      =   200;

    const TOKEN_ID = '_token_id_by_PageMC';

    /** @var object             object for Pager.  */
    protected $controller = NULL;

    /** @var string             name of action in $_REQUEST */
    protected $act_name = '_act';

    /** @var string             default method name is act_index  */
    protected $default  = 'index';

    /** @var \wsCore\Html\PageView           view object...  */
    protected $view = NULL;

    /** @var \wsCore\Web\Session */
    public $session;

    /** @var \wsCore\Web\Request */
    public $request;
    // +-----------------------------------------------------------+
    /**
     * starts PageMC with object as Controller.
     *
     * @param \wsCore\Web\Request $request
     * @param \wsCore\Web\Session $session
     * @DimInjection get \wsCore\Web\Request
     * @DimInjection fresh Session
     */
    public function __construct( $request, $session=null )
    {
        $this->request = $request;
        $this->session = $session;
        if( !isset( $_SESSION[ static::TOKEN_ID ] ) ) $_SESSION[ static::TOKEN_ID ] = array();
    }

    /**
     * @param object $controller
     * @return PageMC
     */
    public function setController( $controller ) {
        $this->controller = $controller;
        return $this;
    }
    // +-----------------------------------------------------------+
    /**
     * @param string $action
     * @param \wsCore\Html\PageView|array $view
     * @throws PageMcException
     */
    public function run( $action, $view )
    {
        $this->view = $view;
        $method = 'act_' . $action;
        try
        {
            $this->view->set( 'currAction', $action );
            if( !method_exists( $this->controller, $method ) )
                throw new PageMcException( "invalid action: $action" );

            if( method_exists( $this->controller, 'pre_action' ) ) {
                call_user_func( array( $this->controller, 'pre_action' ), $this );
            }
            call_user_func( array( $this->controller, $method ), $view );
        }
        catch( PageMcException $e ) {
            $this->error( $e->getMessage() );
        }
    }

    public function getActFromPost() {
        $name = $this->act_name;
        $act  = $this->request->getPost( $name );
        if( !$act ) $act = $this->default;
        return $act;
    }
    /**
     * @param string $message
     */
    public function error( $message ) {
        $this->view->error( $message );
    }
    // +-----------------------------------------------------------+
    //  managing actions
    // +-----------------------------------------------------------+
    /**
     * @param string $act
     */
    public function nextAct( $act ) {
        $this->view[ $this->act_name ] = $act;
    }
    // +-----------------------------------------------------------+
    // token for Cross Site Resource Forage
    // +-----------------------------------------------------------+
    /**
     * push token into session data. max 20.
     */
    public function pushToken() {
        $this->session->pushToken();
    }

    /**
     * checks token from post against session data.
     *
     * @return bool
     */
    public function verifyToken() {
        return $this->session->verifyToken();
    }
    // +-----------------------------------------------------------+
}

/**
$obj = new ExampleController();
$page = new PageMC( $obj );
 **/