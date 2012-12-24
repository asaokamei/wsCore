<?php
namespace wsModule\Alt\Web;

/**
 * simple front-end mini-controller.
 * mostly from PerfectPHP book.
 */
class FrontMC
{
    /** @var \WScore\DiContainer\Dimplet */
    protected $container;

    /** @var \wsModule\Alt\Web\Response */
    public $response;
    
    public $request;
    
    /** @var array */
    public $parameter = array();
    
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
    public function setDefaultParameter( $parameter )
    {
        $this->parameter = $parameter;
    }

    /**
     * @param array $parameter
     * @throws \RuntimeException
     */
    public function run( $parameter=null )
    {
        try 
        {
            // set up parameter from default. 
            $this->parameter = array_merge( $this->parameter, $parameter );
            if( !isset( $this->parameter[ 'controller' ] ) ) {
                throw new \RuntimeException( 'No controller is set');
            }
            // create controller object. 
            $controller_name  = $this->parameter[ 'controller' ] . 'Controller';
            if( isset( $this->parameter[ 'namespace' ] ) && $this->parameter[ 'namespace' ] ) {
                $controller_name = $this->parameter[ 'namespace' ] . '\\' . ucfirst( $controller_name );
            }
            $controller       = $this->container->fresh( $controller_name );
            // set up pre_action method if exists.
            if( method_exists( $controller, 'pre_action' ) ) {
                $controller->pre_action( $this );
            }
            // do the action.
            $action  = 'act' . ucwords( $this->parameter[ 'action' ] );
            $content = $controller->$action( $this->parameter );
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
            $message  = $e->getMessage() . '<br />';
            $message .= 'file:' . $e->getFile() . ', line#' . $e->getLine();
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