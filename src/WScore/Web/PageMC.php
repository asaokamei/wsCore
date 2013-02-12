<?php
namespace WScore\Web;

/**
 * todo: use FrontMC for PageMC. should remove this class.
 * todo: should move Web folder to Alt.
 */
class PageMcException extends \Exception {}

class PageMC
{
    /** @var object             object for Pager.  */
    protected $controller = null;

    /** @var string             name of action in $_REQUEST */
    protected $act_name = '_act';

    /** @var string             default method name is act_index  */
    protected $default  = 'index';

    /** @var \WScore\Html\PageView           view object...  */
    protected $view = null;

    /** @var \WScore\Web\Session */
    public $session;
    // +-----------------------------------------------------------+
    /**
     * starts PageMC with object as Controller.
     *
     * @param \WScore\Web\Session $session
     * @DimInjection fresh Session
     */
    public function __construct( $session )
    {
        $this->session = $session;
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
     * @param \WScore\Html\PageView|array $view
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
        $act  = isset( $_POST[ $this->act_name ] ) ? $_POST[ $this->act_name ] : $this->default;
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