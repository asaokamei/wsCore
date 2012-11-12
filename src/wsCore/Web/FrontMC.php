<?php
namespace wsCore\Web;

/**
 * simple front-end mini-controller.
 * mostly from PerfectPHP book.
 */
class FrontMC
{
    /** @var \wsCore\DiContainer\Dimplet */
    protected $container;

    /** @var \wsCore\Web\Request */
    public $request;

    /** @var \wsCore\Web\Router */
    public $router;

    /** @var \wsCore\Web\Response */
    public $response;

    public $namespace;

    public $debug = false;

    /**
     * @param \wsCore\DiContainer\Dimplet $container
     * @param \wsCore\Web\Request $request
     * @param \wsCore\Web\Router  $router
     * @param \wsCore\Web\Response  $response
     * @DimInjection get Container
     * @DimInjection Fresh \wsCore\Web\Request
     * @DimInjection Fresh \wsCore\Web\Router
     * @DimInjection Fresh \wsCore\Web\Response
     */
    public function __construct( $container, $request, $router, $response )
    {
        $this->container = $container;
        $this->request   = $request;
        $this->router    = $router;
        $this->response  = $response;
    }

    public function run()
    {
        try {
            $params = $this->router->match( $this->request->getPathInfo() );
            if( $params === false ) {
                throw new \RuntimeException( 'No route found for ' . $this->request->getPathInfo() );
            }

            $controller_name  = $params[ 'controller' ];
            $controller_class = $this->namespace . '\\' . ucfirst( $controller_name ) . 'Controller';
            $controller       = $this->container->fresh( $controller_class );
            // set up pre_action method if exists.
            if( method_exists( $controller, 'pre_action' ) ) {
                $controller->pre_action( $this );
            }
            // do the action.
            $action  = 'act' . ucwords( $params[ 'action' ] );
            $content = $controller->$action( $action, $params );
            $this->response->setContent( $content );

        } catch( \RuntimeException $e ) {
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
        $message = 'Page Not Found.';
        if( $this->debug ) {
            $message = $e->getMessage() . '<br />';
            ob_start();
            var_dump( $e->getTrace() );
            $message .= ob_get_clean();
        }

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