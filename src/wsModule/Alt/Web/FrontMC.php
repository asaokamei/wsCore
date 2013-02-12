<?php
namespace wsModule\Alt\Web;

class FrontMcNotFoundException extends \Exception {}

/**
 * todo: set multiple routes and loop through them.
 *
 * simple front-end mini-controller.
 * mostly from PerfectPHP book.
 */
class FrontMC
{
    /** @var \WScore\DiContainer\Dimplet */
    protected $container;

    /** @var \wsModule\Alt\Web\Response */
    public $response;
    
    /** @var \wsModule\Alt\Web\Request */
    public $request;

    /** @var \wsModule\Alt\Web\Router */
    public $router;

    /** @var array */
    public $parameter = array(
        'method' => 'get',
    );
    
    /**
     * @param \WScore\DiContainer\Dimplet $container
     * @param \wsModule\Alt\Web\Response  $response
     * @DimInjection get Container
     * @DimInjection Fresh \wsModule\Alt\Web\Response
     */
    public function __construct( $container, $response )
    {
        $this->container = $container;
        $this->response  = $response;
    }

    /**
     * @param array $parameter
     */
    public function setDefaultParameter( $parameter ) {
        $this->parameter = array_merge( $this->parameter, $parameter );
    }

    /**
     * @param array $parameter
     * @throws FrontMcNotFoundException
     */
    public function run( $parameter=null )
    {
        try {
            
            // set up parameter from default. 
            $this->parameter = array_merge( $this->parameter, $parameter );
            if( !isset( $this->parameter[ 'controller' ] ) ) {
                throw new FrontMcNotFoundException( 'No controller is set' );
            }
            $this->parameter[ 'method' ] = $this->request->getMethod();

            // create controller object. 
            $controller_name = $this->parameter[ 'controller' ] . 'Controller';
            if( isset( $this->parameter[ 'namespace' ] ) && $this->parameter[ 'namespace' ] ) {
                $controller_name = $this->parameter[ 'namespace' ] . '\\' . ucfirst( $controller_name );
            }
            if ( !class_exists( $controller_name ) ) {
                throw new FrontMcNotFoundException( 'no such class: ' . $controller_name );
            }
            $controller = $this->container->get( $controller_name );

            // set up pre_action method if exists.
            if( method_exists( $controller, 'pre_action' ) ) {
                $controller->pre_action( $this );
            }
            // do the action.
            $action = $this->parameter[ 'method' ] . ucwords( $this->parameter[ 'action' ] );
            if( !method_exists( $controller, $action ) ) {
                throw new FrontMcNotFoundException( 'no such method: ' . $action );
            }
            // -------- run --------
            $content = $controller->$action( $this->parameter );
            // -------- end --------
            $this->response->setContent( $content );

        } catch( FrontMcNotFoundException $e ) {
            $this->render404Page( $e );
        }

        $this->response->send();
    }

    /**
     * @param \RuntimeException $e
     */
    protected function render404Page( $e )
    {
        $this->response->setStatusCode( 404, 'Not Found' );
        $message = '<h1>Page Not Found</h1>';
        $message .= '<p>' . $e->getMessage() . '</p>';
        ob_start();
        var_dump( $this->parameter );
        $message .= ob_get_clean();

        $this->response->setContent( <<< END_OF_HTML
        <!DOCTYPE html>
        <html>
          <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>404</title>
          </head>
          <body>
            {$message}
          </body>
        </html>
END_OF_HTML
        );
    }
}