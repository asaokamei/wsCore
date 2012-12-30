<?php
namespace wsModule\Alt\Dci\Web;

class Context implements ContextInterface
{
    /** @var array                          data to register as session data */
    protected $registeredData = array();

    /** @var \WScore\Web\Session             saves itself and token for CSRF */
    protected $session;
    
    /** @var array */
    protected $contexts = array();
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Web\Session   $session
     * @return \wsModule\Alt\Dci\Web\Context
     * @DimInjection Get Session
     */
    public function __construct( $session )
    {
        $this->session = $session;
    }

    /**
     * @param string $name
     * @param \wsModule\Alt\Dci\Web\ContextInterface $context
     */
    public function setContext( $name, $context ) {
        $this->contexts[ $name ] = $context;
    }

    /**
     * @param string $name
     * @return \wsModule\Alt\Dci\Web\ContextInterface
     * @throws \RuntimeException
     */
    public function context( $name ) {
        if( array_key_exists( $name, $this->contexts ) ) return $this->contexts[ $name ];
        throw new \RuntimeException( "No such context: {$name}" );
    }
    // +----------------------------------------------------------------------+
    //  run the context. 
    // +----------------------------------------------------------------------+
    /**
     * @param mixed       $entity
     * @param string      $action
     * @param null|string $form
     * @param null|string $prevForm
     * @return mixed
     */
    public function run( $entity, $action, $form=null, $prevForm=null )
    {
        $this->loadRegistered();
        $return = $this->main( $entity, $action, $form, $prevForm );
        $this->saveRegistered();
        return $return;
    }

    /**
     * @param mixed       $entity
     * @param string      $action
     * @param null|string $form
     * @param null|string $prevForm
     * @return mixed
     */
    protected function main( $entity, $action, $form, $prevForm ) {
        return $entity;
    }

    /**
     * load itself from session
     *
     * @throws \RuntimeException
     */
    private function loadRegistered()
    {
        $class = $this->getInstanceName();
        if( $src = $this->session->get( $class ) ) {
            $this->registeredData = unserialize( $src );
        }
        throw new \RuntimeException( 'Object not saved: '.$class );
    }

    /**
     * saves the instance to session.
     */
    private function saveRegistered()
    {
        $class = $this->getInstanceName();
        $this->session->set( $class, serialize( $this->registeredData ) );
    }

    /**
     * @return string
     */
    private function getInstanceName() {
        $class = str_replace( '\\', '__', get_called_class() );
        return $class;
    }
    // +----------------------------------------------------------------------+
    //  manage variables
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @param mixed $data
     */
    protected function registerData( $name, $data ) {
        $this->registeredData[ $name ] = $data;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function restoreData( $name ) {
        if( array_key_exists( $name, $this->registeredData ) ) {
            return $this->registeredData[ $name ];
        }
        return null;
    }

    /**
     * clears registered data
     */
    protected function clearData() {
        $this->registeredData = array();
    }

    /**
     * register pin with a name. 
     * 
     * @param string $name
     */
    protected function registerPin( $name ) {
        $pinPoint = '_pin_' . $name;
        $this->registerData( $pinPoint, true );
    }

    /**
     * checks if pin point with the name is registered. 
     * if pin point is not registered, returns false, AND registers the pin. 
     * 
     * @param $name
     * @return bool
     */
    protected function checkPin( $name )
    {
        $pinPoint = '_pin_' . $name;
        $pinned   = false;
        if( $this->restoreData( $pinPoint ) ) {
            $pinned = true;
        }
        $this->registerPin( $name );
        return $pinned;
    }
    // +----------------------------------------------------------------------+
}